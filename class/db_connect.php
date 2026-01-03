<?php
// db_connect.php
// Centralized DB connection.
// Override defaults with environment variables if needed:
// - QUIZ_DB_HOST, QUIZ_DB_PORT, QUIZ_DB_USER, QUIZ_DB_PASS, QUIZ_DB_NAME

// Ensure sessions work for API endpoints that include only this file.
if (session_status() === PHP_SESSION_NONE) {
	ini_set('session.use_strict_mode', '1');
	ini_set('session.use_only_cookies', '1');
	ini_set('session.cookie_httponly', '1');

	$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

	// SameSite=Lax keeps typical navigation working while reducing CSRF risk.
	session_set_cookie_params([
		'lifetime' => 0,
		'path' => '/',
		'secure' => $isHttps,
		'httponly' => true,
		'samesite' => 'Lax',
	]);

	session_start();
}

if (!headers_sent()) {
	header('X-Frame-Options: DENY');
	header('X-Content-Type-Options: nosniff');
	header('Referrer-Policy: no-referrer');
	header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

	// Keep CSP compatible with current codebase (inline scripts exist in admin pages).
	header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'; connect-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'");
}

$dbHost = getenv('QUIZ_DB_HOST') ?: '127.0.0.1';
$dbPort = (int)(getenv('QUIZ_DB_PORT') ?: 3306);
$dbUser = getenv('QUIZ_DB_USER') ?: 'root';
$dbPass = getenv('QUIZ_DB_PASS') ?: '';
$dbName = getenv('QUIZ_DB_NAME') ?: 'quiz_database';

try {
	$dbConnect = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
	if ($dbConnect->connect_errno) {
		throw new mysqli_sql_exception($dbConnect->connect_error, $dbConnect->connect_errno);
	}

	// Ensure consistent encoding
	$dbConnect->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
	http_response_code(500);
	error_log('MySQL connection failed: ' . $e->getMessage());
	echo "Database connection failed. Ensure MySQL/MariaDB is running and the configured host/port/credentials are correct.";
	exit();
}
?>