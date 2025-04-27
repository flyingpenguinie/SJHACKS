<?php
// createaccount.php
// Uses existing 'users' table in 'pathfinder' database to register a new user (employee or employer)

session_start();

$host      = 'localhost';
$dbUser    = 'db_user';       // your DB username
$dbPass    = 'db_pass';       // your DB password
$dbName    = 'pathfinder';    // ensure this matches your database name

// Connect via MySQLi
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $userType   = $_POST['user_type'];  // 'employee' or 'employer'

    // Validate account type
    if (!in_array($userType, ['employee', 'employer'])) {
        $errorMsg = 'Please select a valid account type.';
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $profilePic   = 'default_avatar.jpg';

        // Insert into 'users'
        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password_hash, profile_pic, user_type) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $email, $passwordHash, $profilePic, $userType);

        if ($stmt->execute()) {
            // Redirect based on account type
            if ($userType === 'employee') {
                header('Location: EmployeeHomepage.php');
            } else {
                header('Location: EmployerHomepage.php');
            }
            exit();
        } else {
            if ($conn->errno === 1062) {
                $errorMsg = "An account with this email already exists.";
            } else {
                $errorMsg = "Error: " . htmlspecialchars($stmt->error);
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Account</title>
    <style>
        /* Full-page centered card over gradient */
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
            text-align: center;
            color: #4b6cb7;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }
        label {
            display: block;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.25rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }
        input:focus, select:focus {
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
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Create Account</h2>
        <?php if (!empty($errorMsg)): ?>
            <div class="error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>
        <form method="post">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required />

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required />

            <label for="user_type">Account Type</label>
            <select id="user_type" name="user_type" required>
                <option value="employee">Employee</option>
                <option value="employer">Employer</option>
            </select>

            <button class="btn" type="submit">Create Account</button>
        </form>
    </div>
</body>
</html>
