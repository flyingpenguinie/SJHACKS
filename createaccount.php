<?php
// createaccount.php
// Uses existing 'users' table in 'pathfinder' database to register a new user (employee or employer)

session_start();

// DB config
$host   = 'localhost';
$dbUser = 'root';
$dbPass = '';            // XAMPP default: empty
$dbName = 'pathfinder';  // your database

// connect
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // grab & trim inputs
    $first_name = trim($_POST['first_name']  ?? '');
    $last_name  = trim($_POST['last_name']   ?? '');
    $email      = trim($_POST['email']       ?? '');
    $password   = $_POST['password']         ?? '';
    $role       = $_POST['user_type']        ?? '';  // 'employee' or 'employer'

    // basic validation
    if (!$first_name || !$last_name || !$email || !$password) {
        $errorMsg = 'Please fill in all fields.';
    }
    elseif (!in_array($role, ['employee','employer'])) {
        $errorMsg = 'Please select a valid account type.';
    }

    if (empty($errorMsg)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $profilePic   = 'default_avatar.jpg';

        // insert into users
        $stmt = $conn->prepare(
            "INSERT INTO users
               (first_name, last_name, email, password_hash, profile_pic, role)
             VALUES (?,          ?,         ?,     ?,             ?,           ?)"
        );
        $stmt->bind_param(
            "ssssss",
            $first_name,
            $last_name,
            $email,
            $passwordHash,
            $profilePic,
            $role
        );

        if ($stmt->execute()) {
            // at this point, your AFTER INSERT trigger will
            // create the matching row in employees or employers

            // redirect to the appropriate homepage
            if ($role === 'employee') {
                header('Location: signin.php');
            } else {
                header('Location: signin.php');
            }
            exit;
        } else {
            // duplicate email?
            if ($conn->errno === 1062) {
                $errorMsg = 'An account with this email already exists.';
            } else {
                $errorMsg = 'Database error: ' . htmlspecialchars($stmt->error);
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Account</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: linear-gradient(135deg,#4b6cb7 0%,#182848 100%);
      display: flex; align-items: center; justify-content: center;
      height: 100vh; color: #333;
    }
    .card {
      background: #fff; padding: 2rem; border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      width: 100%; max-width: 400px;
      animation: fadeInUp 0.6s ease-out;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    h2 { text-align: center; color: #4b6cb7; margin-bottom: 1.5rem; }
    label { display: block; margin-top: 1rem; font-size: .9rem; color: #555; }
    input, select {
      width: 100%; padding: .75rem; margin-top: .25rem;
      border: 1px solid #ccc; border-radius: 6px;
      font-size: 1rem; transition: border-color .2s;
    }
    input:focus, select:focus { border-color: #4b6cb7; outline: none; }
    .btn {
      margin-top: 1.5rem; width: 100%; padding: .75rem;
      background: linear-gradient(135deg,#6a8de3 0%,#3a5f9e 100%);
      border: none; border-radius: 8px; color: #fff;
      font-size: 1rem; cursor: pointer;
      transition: opacity .2s, transform .2s;
    }
    .btn:hover { opacity: .9; transform: translateY(-2px); }
    .error {
      margin-top: 1rem; padding: .75rem;
      background: #fdecea; color: #b00020; border-radius: 6px;
      text-align: center; font-size: .9rem;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Create Account</h2>
    <?php if ($errorMsg): ?>
      <div class="error"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>
    <form method="post">
      <label for="first_name">First Name</label>
      <input type="text" id="first_name" name="first_name" required />

      <label for="last_name">Last Name</label>
      <input type="text"  id="last_name"  name="last_name"  required />

      <label for="email">Email</label>
      <input type="email" id="email"      name="email"      required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password"   required />

      <label for="user_type">Account Type</label>
      <select id="user_type" name="user_type" required>
        <option value="">— Select —</option>
        <option value="employee">Employee</option>
        <option value="employer">Employer</option>
      </select>

      <button class="btn" type="submit">Create Account</button>
    </form>
  </div>
</body>
</html>