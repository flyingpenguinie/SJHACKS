<?php
session_start();

$host    = 'localhost';
$dbUser  = 'root';    // your DB username
$dbPass  = '';    // your DB password
$dbName  = 'pathfinder'; // ensure this matches your database name

// Connect via MySQLi
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch hashed password and user type from users table
    $stmt = $conn->prepare("SELECT user_id, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashedPassword, $userType);

    if ($stmt->fetch() && password_verify($password, $hashedPassword)) {
        $_SESSION['user_id'] = $id;
        // Redirect based on user type
        if ($userType === 'employee') {
            header('Location: EmployeeHomepage.php');
        } else {
            header('Location: EmployerHomepage.php');
        }
        exit();
    } else {
        $errorMsg = 'Invalid credentials. Please try again.';
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <style>
        /* Full-page grid centered */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }
        /* Card container */
        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 {
            margin-bottom: 1rem;
            text-align: center;
            color: #4b6cb7;
            font-size: 1.75rem;
            letter-spacing: 1px;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #555;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.25rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }
        input:focus {
            border-color: #4b6cb7;
            outline: none;
        }
        .btn {
            margin-top: 1.5rem;
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #6a8de3 0%, #3a5f9e 100%);
            border: none;
            border-radius: 8px;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        .error {
            margin-top: 1rem;
            padding: 0.75rem;
            background: #fdecea;
            color: #b00020;
            border-radius: 6px;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Sign In</h2>
        <?php if (!empty($errorMsg)): ?>
            <div class="error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button class="btn" type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>
