<?php
include 'config.php';
include 'auth.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Wildlife Conservation Monitoring System</h1>

    <p>
        Welcome, <b><?php echo $_SESSION['username']; ?></b>
        |
        Role: <b><?php echo $_SESSION['role']; ?></b>
    </p>

    <div class="menu">

	<?php if (isAdmin()) { ?>
   	 <a href="species.php">Species</a>
   	 <a href="animals.php">Animals</a>
	<?php } ?>

	<?php if (isAdmin() || isRanger()) { ?>
    	<a href="observations.php">Observations</a>
	<?php } ?>

	<a href="reports.php">Reports</a>

	<a href="logout.php">Logout</a>

    </div>
</div>

</body>
</html>