<?php
session_start();

// ============================================
// CONNECT TO SUPABASE (CLOUD DATABASE)
// Using environment variables for security
// ============================================

// Get connection details from environment (Render sets these)
$host = getenv('DB_HOST') ?: "aws-1-ap-southeast-1.pooler.supabase.com";
$port = getenv('DB_PORT') ?: "5432";
$database = getenv('DB_NAME') ?: "postgres";
$user = getenv('DB_USER') ?: "postgres.gdfymhqmlwenvyncqktt";
$password = getenv('DB_PASSWORD') ?: "b_tUyAvk5-,*pca";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Log error instead of showing to users in production
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection error. Please try again later.");
}
?>