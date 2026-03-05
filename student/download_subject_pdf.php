<?php
session_start();
require_once '../db_connect.php';
require_once __DIR__ . '/../vendor/dompdf/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';
if ($subject === '') {
    renderError('Please select a subject before requesting the PDF report.');
}

$student_id = (int)$_SESSION['student_id'];
$student = fetchStudentProfile($conn, $student_id);

if (!$student) {
    renderError('Student record could not be found.', 404);
}

$can_download = isset($student['can_download']) ? (int)$student['can_download'] : 0;
if (!$can_download) {
    renderError('PDF downloads have been disabled for your account. Please contact the administrator.');
}

$semester = isset($student['semester']) ? $student['semester'] : '';
$branch = isset($student['branch']) ? $student['branch'] : '';
if (!isSubjectAssignedToStudent($conn, $semester, $branch, $subject)) {
    renderError('The selected subject is not assigned to your current semester or branch.', 403);
}


$all_submissions = fetchSubjectSubmissions($conn, $student_id, $subject);
$submissions = array_filter($all_submissions, function($sub) {
    return isset($sub['verification_status']) && $sub['verification_status'] === 'Verified';
});

if (empty($submissions)) {
    renderError('No completed experiments found for ' . htmlspecialchars($subject) . '. Only completed experiments can be downloaded.');
}

$rootPath = projectRoot();
$rootUrl = projectRootUrl();

// Debug logging
enableDebugLogging();

$html = buildReportHtml($student, $subject, $submissions, $rootUrl);

// DEBUG: Save HTML for inspection
$logDir = $rootPath . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
file_put_contents($logDir . '/pdf_html_debug.html', $html);

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('defaultMediaType', 'print');
$options->set('defaultPaperSize', 'A4');
$options->setChroot($rootPath);
$options->set('defaultFont', 'dejavu sans');

// Enable CSS float and font subsetting for better image handling
$options->set('enableCssFloat', true);
$options->set('enableFontSubsetting', true);

// Allow all protocols
$options->set('allowedProtocols', array('file://', 'http://', 'https://', 'data:'));

// Configure temp directory with write permissions
$tempDir = sys_get_temp_dir() . '/dompdf_temp_' . uniqid();
if (!is_dir($tempDir)) {
    @mkdir($tempDir, 0755, true);
}
$options->set('tempDir', $tempDir);

// Set font cache directories
$fontCacheDir = $rootPath . '/font_cache';
if (!is_dir($fontCacheDir)) {
    @mkdir($fontCacheDir, 0755, true);
}
$options->set('fontCache', $fontCacheDir);

$fontDir = $rootPath . '/fonts';
if (!is_dir($fontDir)) {
    @mkdir($fontDir, 0755, true);
}
$options->set('fontDir', $fontDir);

// Set log file
$logFile = prepareDompdfLogFile($rootPath);
if ($logFile) {
    $options->setLogOutputFile($logFile);
}

$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'portrait');

try {
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->render();
    
    $pdfContent = $dompdf->output();
    storeSubjectReport($conn, $student_id, $subject, $pdfContent);

    $roll_number = isset($student['roll_number']) ? $student['roll_number'] : 'student';
    $filename = sprintf(
        'SVEC_%s_%s_%s.pdf',
        sanitizeFileSegment($roll_number),
        sanitizeFileSegment($subject),
        date('Ymd_His')
    );
    
    $dompdf->stream($filename, array('Attachment' => true));
    
} catch (Exception $e) {
    // Log the detailed error
    error_log('PDF Generation Error: ' . $e->getMessage());
    error_log('Stack Trace: ' . $e->getTraceAsString());
    
    // Display user-friendly error
    echo '<h2>PDF Generation Failed</h2>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Please contact administrator or try again later.</p>';
    echo '<p><a href="updated_exp.php">← Back to Dashboard</a></p>';
    
    // Also log to file
    file_put_contents($logDir . '/pdf_errors.log', 
        date('Y-m-d H:i:s') . "\n" . 
        $e->getMessage() . "\n" . 
        $e->getTraceAsString() . "\n\n", 
        FILE_APPEND
    );
}
exit;

function enableDebugLogging() {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    
    // Create logs directory if not exists
    $logDir = projectRoot() . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/php_errors.log');
}

function buildReportHtml($student, $subject, $submissions, $baseHref) {
    $sectionsHtml = '';
    foreach ($submissions as $index => $submission) {
        $sectionsHtml .= renderExperimentSection($submission, $baseHref, $index + 1);
    }

    $css = getBaseCss();
    $baseHrefEsc = htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lab Report</title>
<base href="{$baseHrefEsc}">
{$css}
</head>
<body>
    {$sectionsHtml}
</body>
</html>
HTML;
}

function renderExperimentSection($submission, $baseHref, $expNum) {
    $submissionHtml = prepareSubmissionHtml(isset($submission['submission_data']) ? $submission['submission_data'] : '', $baseHref, $expNum);

    return <<<HTML
<section class="experiment-wrapper">
    <div class="submission-body">
        {$submissionHtml}
    </div>
</section>
HTML;
}

function prepareSubmissionHtml($html, $baseHref, $expNum) {
    if ($html === '') {
        return '<p style="font-size:12pt;color:#000;">No submission content available.</p>';
    }

    // Remove scripts
    $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);

    // Remove document wrappers and conflicting tags
    $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
    $html = preg_replace('/<\/?(html|head|body)[^>]*>/i', '', $html);
    $html = preg_replace('/<meta[^>]+>/i', '', $html);
    $html = preg_replace('/<link[^>]+>/i', '', $html);
    $html = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $html);
    $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);

    // Demote large headings from the submission to keep hierarchy consistent
    $html = preg_replace('/<h1\b([^>]*)>/i', '<h3$1>', $html);
    $html = preg_replace('/<\/h1>/i', '</h3>', $html);
    $html = preg_replace('/<h2\b([^>]*)>/i', '<h4$1>', $html);
    $html = preg_replace('/<\/h2>/i', '</h4>', $html);
    $html = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br><br>', $html);
    
    // Log original image sources for debugging
    preg_match_all('/<img[^>]+(src|data-src)=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
    if (!empty($matches[2])) {
        $logFile = projectRoot() . '/logs/image_sources.log';
        $logData = date('Y-m-d H:i:s') . " - Experiment {$expNum} - Found " . count($matches[2]) . " images:\n";
        foreach ($matches[2] as $src) {
            $logData .= "  Original: " . $src . "\n";
        }
        $logData .= "\n";
        @file_put_contents($logFile, $logData, FILE_APPEND);
    }
    
    // Fix image paths
    $html = fixImagePathsInHtml($html, $baseHref, $expNum);
    
    return $html;
}

function fixImagePathsInHtml($html, $baseUrl, $expNum) {
    // Fix all image tags
    $html = preg_replace_callback(
        '/<img[^>]+(src|data-src)=["\']([^"\']+)["\'][^>]*>/i',
        function ($matches) use ($baseUrl, $expNum) {
            $attr = $matches[1];
            $originalSrc = $matches[0];
            $url = $matches[2];
            
            // Log each image processing
            $logFile = projectRoot() . '/logs/image_processing.log';
            $logData = date('Y-m-d H:i:s') . " - Experiment {$expNum}\n";
            $logData .= "  Processing image: " . $url . "\n";
            
            // If it's not already an absolute URL
            if (!preg_match('#^(https?://|data:|file:|ftp://)#i', $url)) {
                // Convert to absolute URL
                $absolute = absolutizeResourcePath($url, $baseUrl);
                $logData .= "  Converted to: " . $absolute . "\n";
                
                // Check if file exists
                if (preg_match('#^file://#i', $absolute)) {
                    $filepath = str_replace('file:///', '', $absolute);
                    $filepath = preg_replace('#^([A-Z]):#', '$1:', $filepath);
                    if (file_exists($filepath)) {
                        $logData .= "  ✓ File exists (" . filesize($filepath) . " bytes)\n";
                        $logData .= "  ✓ File type: " . mime_content_type($filepath) . "\n";
                    } else {
                        $logData .= "  ✗ File NOT found\n";
                    }
                }
                
                // Replace the src attribute
                $replaced = preg_replace(
                    '/(src|data-src)=["\']' . preg_quote($url, '/') . '["\']/i',
                    $attr . '="' . htmlspecialchars($absolute, ENT_QUOTES) . '"',
                    $originalSrc
                );
                $logData .= "  Replaced HTML\n";
                
                @file_put_contents($logFile, $logData, FILE_APPEND);
                return $replaced;
            }
            
            $logData .= "  Already absolute URL\n";
            @file_put_contents($logFile, $logData, FILE_APPEND);
            return $originalSrc;
        },
        $html
    );
    
    // Also fix background images
    $html = preg_replace_callback(
        '/background(-image)?:\s*url\(["\']?([^"\')]+)["\']?\)/i',
        function ($matches) use ($baseUrl) {
            $url = $matches[2];
            
            if (!preg_match('#^(https?://|data:|file:|ftp://)#i', $url)) {
                $absolute = absolutizeResourcePath($url, $baseUrl);
                return str_replace($url, $absolute, $matches[0]);
            }
            
            return $matches[0];
        },
        $html
    );
    
    return $html;
}

function absolutizeResourcePath($path, $baseHref) {
    $trimmed = trim($path);
    if ($trimmed === '') {
        return $path;
    }

    // If it's already an absolute URL or data URI, return as-is
    if (preg_match('#^(https?://|data:|file:|ftp://)#i', $trimmed)) {
        return $trimmed;
    }

    // Clean up the path
    $clean = str_replace('\\', '/', $trimmed);
    // Remove relative path components (./ and ../ prefixes)
    $clean = preg_replace('#^(\./|\.\./)+#', '', $clean);
    
    // Remove leading slashes
    $clean = ltrim($clean, '/');
    
    // Common directories to check (in order of priority)
    $possiblePaths = array();
    $addCandidate = function($path) use (&$possiblePaths) {
        $normalized = ltrim($path, '/');
        if ($normalized === '') {
            return;
        }
        if (!in_array($normalized, $possiblePaths, true)) {
            $possiblePaths[] = $normalized;
        }
    };
    
    $addCandidate($clean);
    if (stripos($clean, 'college_lab/') !== 0) {
        $addCandidate('college_lab/' . $clean);
    }
    
    $baseDirs = array(
        'uploads',
        'uploads/images',
        'uploads/experiments',
        'student_uploads',
        'images',
        'experiment_data',
        'lab_data'
    );
    foreach ($baseDirs as $dir) {
        $startsWithDir = stripos($clean, $dir . '/') === 0;
        $startsWithPrefixedDir = stripos($clean, 'college_lab/' . $dir . '/') === 0;
        if (!$startsWithDir && !$startsWithPrefixedDir) {
            $addCandidate($dir . '/' . $clean);
            $addCandidate('college_lab/' . $dir . '/' . $clean);
        }
    }
    
    $filename = basename($clean);
    if ($filename !== $clean) {
        $addCandidate('uploads/' . $filename);
        $addCandidate('images/' . $filename);
        $addCandidate('college_lab/uploads/' . $filename);
        $addCandidate('college_lab/images/' . $filename);
    }
    
    // If running on a web server
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
        
        // Try each possible path
        foreach ($possiblePaths as $testPath) {
            $fullPath = rtrim($docRoot, '/') . '/' . $testPath;
            if (file_exists($fullPath) && is_readable($fullPath)) {
                // Verify it's an image file
                $mime = mime_content_type($fullPath);
                if (strpos($mime, 'image/') === 0) {
                    // Log found image
                    $logFile = projectRoot() . '/logs/image_found.log';
                    @file_put_contents($logFile, 
                        date('Y-m-d H:i:s') . " - Found: {$testPath}\n" .
                        "  Original: {$path}\n" .
                        "  MIME: {$mime}\n" .
                        "  Size: " . filesize($fullPath) . " bytes\n\n",
                        FILE_APPEND
                    );
                    
                    return $protocol . $host . '/' . $testPath;
                }
            }
        }
        
        // If not found, return the most likely path
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptDir !== '/' && $scriptDir !== '.') {
            $scriptDir = rtrim($scriptDir, '/');
            return $protocol . $host . $scriptDir . '/' . $clean;
        }
        
        return $protocol . $host . '/' . $clean;
    }
    
    // For local/command-line environments (XAMPP)
    $possibleRoots = array(
        'C:/xampp1/htdocs',
        'C:/xampp/htdocs',
        projectRoot(),
        dirname(projectRoot()),
        getcwd()
    );
    
    foreach ($possibleRoots as $root) {
        if (!$root) continue;
        
        foreach ($possiblePaths as $testPath) {
            $fullPath = rtrim($root, '/\\') . DIRECTORY_SEPARATOR . 
                       str_replace('/', DIRECTORY_SEPARATOR, $testPath);
            
            if (file_exists($fullPath) && is_readable($fullPath)) {
                // Verify it's an image file
                $mime = @mime_content_type($fullPath);
                if ($mime && strpos($mime, 'image/') === 0) {
                    return pathToFileUrl($fullPath);
                }
            }
        }
    }
    
    // If still not found, try direct file:// URL as last resort
    $directPaths = array(
        'C:/xampp1/htdocs/college_lab/' . $clean,
        'C:/xampp/htdocs/college_lab/' . $clean,
        projectRoot() . '/' . $clean
    );
    
    foreach ($directPaths as $directPath) {
        if (file_exists($directPath) && is_readable($directPath)) {
            return pathToFileUrl($directPath);
        }
    }
    
    // Log not found
    $logFile = projectRoot() . '/logs/image_not_found.log';
    @file_put_contents($logFile, 
        date('Y-m-d H:i:s') . " - NOT FOUND: {$path}\n" .
        "  Cleaned: {$clean}\n" .
        "  Base Href: {$baseHref}\n\n",
        FILE_APPEND
    );
    
    // Return original path (will show broken image in PDF)
    return $clean;
}

function pathToFileUrl($path) {
    $real = realpath($path) ?: $path;
    $real = str_replace('\\', '/', $real);
    
    // Windows paths
    if (DIRECTORY_SEPARATOR === '\\') {
        // Handle Windows drive letters
        if (preg_match('/^([A-Za-z]):/', $real, $matches)) {
            $drive = strtoupper($matches[1]);
            $rest = substr($real, 2);
            return "file:///$drive$rest";
        }
        return 'file://' . $real;
    }
    
    // Unix paths
    return 'file://' . $real;
}

function formatMarks($value) {
    if ($value === null || $value === '') {
        return '—';
    }
    $number = number_format((float)$value, 2, '.', '');
    $number = rtrim(rtrim($number, '0'), '.');
    return $number . ' / 10';
}

function getBaseCss() {
    return '<style>
    @page { margin: 15mm 18mm; }
    * { box-sizing: border-box; }
    body { font-family: "Times New Roman", "DejaVu Serif", serif; font-size: 12pt; color: #000; background: #fff; margin: 0; }
    h1, h2, h3, h4 { font-weight: bold; }
    .experiment-wrapper { page-break-after: always; padding-bottom: 12mm; }
    .experiment-wrapper:last-of-type { page-break-after: auto; }
    .submission-body { padding-top: 6px; }
    .submission-body h3 { font-size: 12pt; margin: 0 0 10px; color: #0f172a; }
    .submission-body h4 { font-size: 14pt; margin: 6px 0; color: #111827; }
    .experiment-wrapper table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 11pt; }
    .experiment-wrapper table, .experiment-wrapper th, .experiment-wrapper td { border: 1px solid #000; }
    .experiment-wrapper th, .experiment-wrapper td { padding: 6px 8px; }
    .experiment-wrapper p { margin: 6px 0; }
    .experiment-wrapper img { 
        max-width: 100%; 
        max-height: 300px;
        height: auto; 
        display: block; 
        margin: 10px auto;
        border: 1px solid #ddd;
        page-break-inside: avoid;
    }
    ul, ol { margin: 6px 0 6px 22px; }
    .missing-image { 
        display: block; 
        width: 200px; 
        height: 150px; 
        background: #f0f0f0; 
        border: 1px dashed #ccc; 
        margin: 10px auto; 
        text-align: center; 
        line-height: 150px; 
        color: #666;
        font-style: italic;
    }
    </style>';
}

function fetchStudentProfile($conn, $studentId) {
    $stmt = $conn->prepare('SELECT student_id, name, roll_number, branch, semester, section, can_download FROM students WHERE student_id = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('i', $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return $profile ? $profile : null;
}

function isSubjectAssignedToStudent($conn, $semester, $branch, $subject) {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM semester_subject_assignments WHERE semester = ? AND branch = ? AND subject_name = ?');
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('sss', $semester, $branch, $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return isset($row['total']) ? (int)$row['total'] > 0 : false;
}

function fetchSubjectSubmissions($conn, $studentId, $subject) {
    $sql = "SELECT s.submission_id, s.submission_data, s.verification_status, s.submitted_date, s.verification_date,
                   s.marks_obtained, s.feedback, s.retake_count, s.is_retake, e.experiment_number, e.experiment_name,
                   emp.name AS faculty_name
            FROM submissions s
            INNER JOIN (
                SELECT experiment_id, MAX(submitted_date) AS latest_date
                FROM submissions
                WHERE student_id = ?
                GROUP BY experiment_id
            ) latest ON latest.experiment_id = s.experiment_id AND latest.latest_date = s.submitted_date
            INNER JOIN experiments e ON e.Id = s.experiment_id
            LEFT JOIN employees emp ON emp.employee_id = s.employee_id
            WHERE s.student_id = ? AND e.subject = ?
            ORDER BY e.experiment_number ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return array();
    }

    $stmt->bind_param('iis', $studentId, $studentId, $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
    $stmt->close();

    foreach ($rows as &$row) {
        $rawHtml = isset($row['submission_data']) ? $row['submission_data'] : '';
        $decoded = str_replace(array('\\n', '\n'), "\n", $rawHtml);
        $decoded = stripslashes($decoded);
        $row['submission_data'] = $decoded;
    }

    return $rows;
}

function sanitizeFileSegment($value) {
    $sanitized = preg_replace('/[^A-Za-z0-9]+/', '_', $value);
    $sanitized = trim($sanitized, '_');
    return $sanitized !== '' ? $sanitized : 'report';
}

function projectRoot() {
    $root = realpath(__DIR__ . '/..') ?: __DIR__ . '/..';
    if (basename($root) === 'college_lab') {
        $root = dirname($root);
    }
    return $root;
}

function projectRootUrl() {
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        
        if ($scriptDir === '/' || $scriptDir === '\\' || $scriptDir === '.') {
            return $protocol . '://' . $host . '/';
        }
        
        $scriptDir = rtrim($scriptDir, '/');
        return $protocol . '://' . $host . $scriptDir . '/';
    }
    
    $root = projectRoot();
    return pathToFileUrl($root) . '/';
}

function renderError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Report Unavailable</title>';
    echo '<style>body{font-family:Segoe UI,Arial,sans-serif;background:#f8fafc;padding:40px;color:#0f172a;}';
    echo '.card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:30px;max-width:600px;margin:0 auto;}';
    echo '.card h2{margin-top:0;color:#1e3a8a;} .card a{color:#2563eb;text-decoration:none;font-weight:600;}</style>';
    echo '</head><body><div class="card">';
    echo '<h2>Unable to generate PDF</h2>';
    echo '<p>' . htmlspecialchars($message) . '</p>';
    echo '<p><a href="updated_exp.php">← Back to dashboard</a></p>';
    echo '</div></body></html>';
    exit();
}

function prepareDompdfLogFile($rootPath) {
    $logDir = $rootPath . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logPath = $logDir . '/dompdf.log';
    if (!file_exists($logPath)) {
        if (@touch($logPath) === false) {
            return null;
        }
        @chmod($logPath, 0666);
    }
    
    if (!is_writable($logPath)) {
        return null;
    }
    
    return $logPath;
}

function storeSubjectReport($conn, $studentId, $subject, $pdfContent) {
    if (!$conn || !$studentId || empty($pdfContent)) {
        return false;
    }

    $reportType = buildSubjectReportType($subject);
    $sql = "INSERT INTO student_reports (student_id, report_type, report_content, generated_date)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                report_content = VALUES(report_content),
                generated_date = NOW()";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('iss', $studentId, $reportType, $pdfContent);
        $success = $stmt->execute();
        $stmt->close();
        if (!$success) {
            logReportStoreError('Failed to upsert subject report: ' . $conn->error);
        }
        return $success;
    }

    logReportStoreError('Unable to prepare subject report insert: ' . $conn->error);
    return false;
}

function buildSubjectReportType($subject) {
    $key = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($subject));
    $key = trim($key, '_');
    if ($key === '') {
        $key = 'GENERAL';
    }
    $reportType = 'SUBJECT_' . $key;
    return substr($reportType, 0, 64);
}

function logReportStoreError($message) {
    $logDir = projectRoot() . '/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/report_store_errors.log';
    $entry = date('Y-m-d H:i:s') . ' - ' . $message . "\n";
    @file_put_contents($logFile, $entry, FILE_APPEND);
}