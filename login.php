<?php
include 'config.php';

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Use PDO syntax instead of MySQLi
    try {
        $sql = "SELECT u.*, r.role_name
                FROM users u
                JOIN roles r ON u.role_id = r.role_id
                WHERE u.username = :username AND u.password = :password";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $password
        ]);
        
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_name'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Wildlife Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-box">
    <h2>Wildlife Conservation System</h2>
    
    <?php if ($error != "") echo "<p class='error'>$error</p>"; ?>
    
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
</div>

</body>
</html>