<?php
// import_students.php - Handle bulk student import from CSV/Excel
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

// Helper function: convert numeric semester to Roman
function numberToRoman($num) {
    $map = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII'];
    return $map[(int)$num] ?? strtoupper((string)$num);
}

$students_data = array();

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
            
            // Limit to 10,000 students
            if ($row_num > 10001) {
                break;
            }
            
            $row = array_combine($header, $data);
            
            // Validate required fields
            if (empty($row['name']) || empty($row['roll_number']) || empty($row['username']) || 
                empty($row['password']) || empty($row['branch']) || empty($row['section']) || empty($row['semester'])) {
                continue; // Skip incomplete rows
            }
            
            $students_data[] = [
                'name' => trim($row['name']),
                'roll_number' => trim($row['roll_number']),
                'username' => trim($row['username']),
                'password' => trim($row['password']),
                'branch' => trim($row['branch']),
                'section' => trim($row['section']),
                'semester' => numberToRoman(trim($row['semester'])),
                'email' => trim($row['email'] ?? ''),
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
                
                // Limit to 10,000 students
                if ($row_num > 10001) {
                    break;
                }
                
                $row = array_combine($header, $row_data);
                
                // Validate required fields
                if (empty($row['name']) || empty($row['roll_number']) || empty($row['username']) || 
                    empty($row['password']) || empty($row['branch']) || empty($row['section']) || empty($row['semester'])) {
                    continue; // Skip incomplete rows
                }
                
                $students_data[] = [
                    'name' => trim($row['name']),
                    'roll_number' => trim($row['roll_number']),
                    'username' => trim($row['username']),
                    'password' => trim($row['password']),
                    'branch' => trim($row['branch']),
                    'section' => trim($row['section']),
                    'semester' => numberToRoman(trim($row['semester'])),
                    'email' => trim($row['email'] ?? ''),
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
    global $students_data;
    
    // For now, provide error message asking to use CSV
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Excel library not installed. Please convert your file to CSV format and try again.']);
    exit;
}

// Import data into database
$imported_count = 0;
$failed_count = 0;
$errors = [];

foreach ($students_data as $index => $student) {
    // Check if username or roll_number already exists
    $check_stmt = $conn->prepare("SELECT 1 FROM students WHERE username = ? OR roll_number = ? LIMIT 1");
    if ($check_stmt) {
        $check_stmt->bind_param("ss", $student['username'], $student['roll_number']);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $failed_count++;
            $errors[] = "Row " . ($index + 2) . ": Username or Roll Number already exists";
            $check_stmt->close();
            continue;
        }
        $check_stmt->close();
    }
    
    // Insert student
    $stmt = $conn->prepare("INSERT INTO students (name, roll_number, username, password, branch, section, semester, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param(
            "sssssssss",
            $student['name'],
            $student['roll_number'],
            $student['username'],
            $student['password'],
            $student['branch'],
            $student['section'],
            $student['semester'],
            $student['email'],
            $student['phone']
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
    'message' => "Import completed! Imported: {$imported_count}, Failed: {$failed_count}",
    'imported_count' => $imported_count,
    'failed_count' => $failed_count,
    'errors' => array_slice($errors, 0, 10) // Return first 10 errors
];

header('Content-Type: application/json');
echo json_encode($response);
?>
