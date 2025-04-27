<?php
session_start();
if (isset($_SESSION['employer_id'])) {
    header('Location: EmployerHomepage.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Sign In - Pathfinder</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2575fc, #6a11cb);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        h2 {
            margin-bottom: 1rem;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .btn {
            padding: 1rem 2rem;
            background-color: #fff;
            color: #2575fc;
            border: none;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            width: 250px;
            text-align: center;
        }
        .btn:hover {
            background-color: #2575fc;
            color: #fff;
        }
    </style>
</head>
<body>

    <h2>Employer Access</h2>
    <div class="button-container">
        <a href="createaccountEmployer.php"><button class="btn">Create Account</button></a>
        <a href="signin.php"><button class="btn">Sign In</button></a>
    </div>

</body>
</html>
