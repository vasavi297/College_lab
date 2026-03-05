<?php
// SUPER STRONG LOGOUT - Clears everything
session_start();

// Destroy session completely
$_SESSION = array();
session_destroy();

// Clear all cookies
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000);
        setcookie($name, '', time()-1000, '/');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <script>
    // NUCLEAR OPTION - Clear everything except announcement read tracking
    (function preserveAnnouncementReadState() {
        const preservedKeys = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('announcement_read_')) {
                preservedKeys.push({ key, value: localStorage.getItem(key) });
            }
        }
        localStorage.clear();
        preservedKeys.forEach(entry => localStorage.setItem(entry.key, entry.value));
    })();
    sessionStorage.clear();
    
    // Disable cache for future
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) caches.delete(name);
        });
    }
    
    // Redirect and prevent back
    history.pushState(null, null, '/college_lab/index.php');
    window.addEventListener('popstate', function() {
        history.pushState(null, null, '/college_lab/index.php');
    });
    
    // Force redirect
    window.location.replace('/college_lab/index.php');
    
    // Final fallback
    setTimeout(function() {
        window.location.href = '/college_lab/index.php';
    }, 100);
    </script>
</head>
<body>
</body>
</html>