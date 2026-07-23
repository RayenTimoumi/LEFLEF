<?php
// Database configuration
define('DB_HOST', '127.0.0.1:3306');
define('DB_NAME', 'projet_integration');
define('DB_USER', 'root');
define('DB_PASS', ''); // In production, use a strong password
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Gestion des Files d\'Attente');
define('BASE_URL', 'http://localhost/projet_integration/'); // Adjust as needed
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('INSTITUTION_LOGO_DIR', UPLOAD_DIR . 'institutions/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('DEBUG', true); // Set to false in production

// Session configuration
define('SESSION_NAME', 'PROJET_INTEGRATION_SESSID');
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_SECURE', false); // Set to true if using HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax');

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => SESSION_SAMESITE
    ]);
    session_start();
}

// Error reporting
if (defined('DEBUG') && DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set custom error and exception handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("[Error $errno] $errstr in $errfile on line $errline");
    if (defined('DEBUG') && DEBUG) {
        echo "<div class='alert alert-danger'>Error: $errstr</div>";
    }
    return true;
});

set_exception_handler(function($e) {
    error_log("Uncaught exception: " . $e->getMessage());
    http_response_code(500);
    if (defined('DEBUG') && DEBUG) {
        echo "<div class='alert alert-danger'>Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
    } else {
        echo "An error occurred. Please try again later.";
    }
    exit();
});

// Create database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// CSRF Protection functions
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token'], $token) && hash_equals($_SESSION['csrf_token'], $token);
}

// Check admin authentication
function checkAdminAuth() {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// File upload function with validation
function uploadFile($file, $directory, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . $file['error']);
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception("File size exceeds maximum allowed size");
    }

    // Verify file type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) {
        throw new Exception("Invalid file type");
    }

    // Create directory if it doesn't exist
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . strtolower($ext);
    $targetPath = rtrim($directory, '/') . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception("Failed to move uploaded file");
    }

    return $filename;
}

// Redirect function with optional status code
function redirect($url, $statusCode = 303) {
    header('Location: ' . $url, true, $statusCode);
    exit();
}

// Flash message system
function flash($name, $message = null) {
    if ($message === null) {
        $message = $_SESSION[$name] ?? null;
        unset($_SESSION[$name]);
        return $message;
    }
    $_SESSION[$name] = $message;
}

// Input sanitization
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}