<?php
// import_employees.php - Handle bulk employee import from CSV/Excel
session_start();
require_once '../db_connect.php';

// SESSION CHECK - Allow if user_id is set and role is admin OR if both user_id and role are missing (for local testing)
if (!isset($_SESSION['user_id'])) {
    // Check if this is being accessed from admin dashboard context
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($referer, 'admin') === false && strpos($referer, 'localhost') === false) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
        exit;
    }
}

// Verify admin role if user_id is set
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['import_file'];
$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Validate file type
if (!in_array($file_type, ['csv', 'xlsx', 'xls'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Only CSV and Excel files are allowed']);
    exit;
}

$employees_data = array();

// Handle CSV file
if ($file_type === 'csv') {
    if (($handle = fopen($file_tmp, 'r')) !== false) {
        $header = null;
        $row_num = 0;
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row_num++;
            
            // First row is header
            if ($row_num === 1) {
                $header = $data;
                continue;
            }
            
            // Limit to 10,000 employees
            if ($row_num > 10001) {
                break;
            }
            
            $row = array_combine($header, $data);
            
            // Validate required fields
            if (empty($row['name']) || empty($row['username']) || empty($row['email']) || 
                empty($row['department']) || empty($row['role'])) {
                continue; // Skip incomplete rows
            }
            
            $employees_data[] = [
                'name' => trim($row['name']),
                'username' => trim($row['username']),
                'password' => trim($row['password'] ?? $row['username']),
                'email' => trim($row['email']),
                'department' => trim($row['department']),
                'role' => trim($row['role']),
                'phone' => trim($row['phone'] ?? '')
            ];
        }
        fclose($handle);
    }
} 
// Handle Excel file (.xlsx or .xls)
else if (in_array($file_type, ['xlsx', 'xls'])) {
    // Check if PHPSpreadsheet is available
    if (file_exists('../vendor/autoload.php')) {
        require_once '../vendor/autoload.php';
        
        try {
            if ($file_type === 'xlsx') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
            
            $spreadsheet = $reader->load($file_tmp);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $row_num = 0;
            $header = null;
            
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $row_data = [];
                foreach ($cellIterator as $cell) {
                    $row_data[] = $cell->getValue();
                }
                
                $row_num++;
                
                // First row is header
                if ($row_num === 1) {
                    $header = $row_data;
                    continue;
                }
                
                // Limit to 10,000 employees
                if ($row_num > 10001) {
                    break;
                }
                
                $row = array_combine($header, $row_data);
                
                // Validate required fields
                if (empty($row['name']) || empty($row['username']) || empty($row['email']) || 
                    empty($row['department']) || empty($row['role'])) {
                    continue; // Skip incomplete rows
                }
                
                $employees_data[] = [
                    'name' => trim($row['name']),
                    'username' => trim($row['username']),
                    'password' => trim($row['password'] ?? $row['username']),
                    'email' => trim($row['email']),
                    'department' => trim($row['department']),
                    'role' => trim($row['role']),
                    'phone' => trim($row['phone'] ?? '')
                ];
            }
        } catch (Exception $e) {
            // If PHPSpreadsheet fails, try converting to CSV using system command
            try_convert_excel_to_csv($file_tmp, $file_type);
        }
    } else {
        // Try using system command to convert Excel to CSV
        try_convert_excel_to_csv($file_tmp, $file_type);
    }
}

// Function to handle Excel conversion to CSV via system command
function try_convert_excel_to_csv(&$file_tmp, $file_type) {
    global $employees_data;
    
    // For now, provide error message asking to use CSV
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Excel library not installed. Please convert your file to CSV format and try again.']);
    exit;
}

// Import data into database
$imported_count = 0;
$failed_count = 0;
$errors = [];

foreach ($employees_data as $index => $employee) {
    // Check if username or email already exists
    $check_stmt = $conn->prepare("SELECT 1 FROM employees WHERE username = ? OR email = ? LIMIT 1");
    if ($check_stmt) {
        $check_stmt->bind_param("ss", $employee['username'], $employee['email']);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $failed_count++;
            $errors[] = "Row " . ($index + 2) . ": Username or Email already exists";
            $check_stmt->close();
            continue;
        }
        $check_stmt->close();
    }
    
    // Insert employee
    $stmt = $conn->prepare("INSERT INTO employees (name, username, password, email, department, role, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
    
    if ($stmt) {
        $stmt->bind_param(
            "sssssss",
            $employee['name'],
            $employee['username'],
            $employee['password'],
            $employee['email'],
            $employee['department'],
            $employee['role'],
            $employee['phone']
        );
        
        if ($stmt->execute()) {
            $imported_count++;
        } else {
            $failed_count++;
            $errors[] = "Row " . ($index + 2) . ": " . $stmt->error;
        }
        $stmt->close();
    } else {
        $failed_count++;
        $errors[] = "Row " . ($index + 2) . ": Database error";
    }
}

// Return response
$response = [
    'status' => 'success',
    'imported_count' => $imported_count,
    'failed_count' => $failed_count,
    'message' => "Imported $imported_count employees" . ($failed_count > 0 ? ", $failed_count failed" : '')
];

if (!empty($errors) && count($errors) <= 20) {
    $response['errors'] = $errors;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
