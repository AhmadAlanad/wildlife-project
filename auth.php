<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function isAdmin() {
    return $_SESSION['role'] == 'Admin';
}

function isRanger() {
    return $_SESSION['role'] == 'Ranger';
}

function isResearcher() {
    return $_SESSION['role'] == 'Researcher';
}
?>