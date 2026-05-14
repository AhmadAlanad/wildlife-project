<?php
session_start();

$host = getenv('DB_HOST') ?: "aws-1-ap-southeast-1.pooler.supabase.com";
$port = "6543";
$database = getenv('DB_NAME') ?: "postgres";
$user = getenv('DB_USER') ?: "postgres.gdfymhqmlwenvyncqktt";
$password = getenv('DB_PASSWORD') ?: "wildliveconservation2026";  // ← Update this!

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require;connect_timeout=10";
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 10
    ]);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Database connection error: " . $e->getMessage());
}
?>