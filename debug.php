<?php
echo "<h2>🔍 Render Environment Debug</h2>";

// Check all environment variables
echo "<h3>Database Environment Variables:</h3>";
$db_vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
foreach ($db_vars as $var) {
    $value = getenv($var);
    if ($value === false) {
        echo "<p style='color:red'>❌ $var: NOT SET</p>";
    } else {
        $display = ($var == 'DB_PASSWORD') ? '********' : $value;
        echo "<p style='color:green'>✅ $var: $display</p>";
    }
}

// Check all PHP variables
echo "<h3>PHP Info:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "</p>";

// Try manual connection
echo "<h3>Test Manual Connection:</h3>";
try {
    $host = getenv('DB_HOST') ?: "aws-1-ap-southeast-1.pooler.supabase.com";
    $port = getenv('DB_PORT') ?: "5432";
    $database = getenv('DB_NAME') ?: "postgres";
    $user = getenv('DB_USER') ?: "postgres.gdfymhqmlwenvyncqktt";
    $password = getenv('DB_PASSWORD') ?: "b_tUyAvk5-*,pca";
    
    echo "<p>Attempting connection to: $host:$port</p>";
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$database;sslmode=require";
    $conn = new PDO($dsn, $user, $password);
    
    echo "<p style='color:green'>✅ SUCCESS! Connected to Supabase!</p>";
    
    // Test query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>📊 Users in database: " . $result['count'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Connection failed: " . $e->getMessage() . "</p>";
}
?>