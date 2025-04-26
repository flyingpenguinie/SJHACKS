<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'pathfinder_db'; // make sure you create this database and employers table

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO employers (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $password);
    if ($stmt->execute()) {
        header('Location: employersignin.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
</head>
<body>
    <h2>Create Employer Account</h2>
    <form method="post">
        Email: <input type="email" name="email" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Create</button>
    </form>
</body>
</html>
