<?php
session_start();
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'pathfinder_db';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM employers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashedPassword);
    if ($stmt->fetch() && password_verify($password, $hashedPassword)) {
        $_SESSION['employer_id'] = $id;
        header('Location: employerhome.php');
        exit();
    } else {
        echo "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
</head>
<body>
    <h2>Employer Sign In</h2>
    <form method="post">
        Email: <input type="email" name="email" required><br><br>
        Password: <input type="password" name="password" required><br><br>
        <button type="submit">Sign In</button>
    </form>
</body>
</html>
