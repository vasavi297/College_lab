<?php
function ensure_desktop_only() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $is_mobile = preg_match('/mobile|android|iphone|ipad|ipod|opera mini|blackberry|iemobile|wpdesktop/i', $ua);
    if ($is_mobile) {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'><style>body{font-family:Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f8fafc;color:#0f172a;} .card{background:#fff;padding:28px 32px;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.06);text-align:center;} h1{margin:0 0 10px;font-size:20px;} p{margin:0;color:#64748b;}</style></head><body><div class='card'><h1>You cannot access this page in mobile view.</h1><p>Please open this experiment on a laptop/desktop.</p></div></body></html>";
        exit();
    }
}
?>
