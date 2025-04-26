<?php
session_start();
if (!isset($_SESSION['employer_id'])) {
    header('Location: employersignin.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employer Home</title>
</head>
<body>
    <h1>Welcome to Your Employer Dashboard!</h1>
    <p>Post jobs, find workers, and help rebuild futures.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
