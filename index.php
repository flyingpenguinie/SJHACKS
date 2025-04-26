<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pathfinder - Find Your Way</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        h1 {
            font-size: 4rem;
            margin-bottom: 0.5rem;
            animation: fadeInDown 1s ease;
        }
        p {
            font-size: 1.5rem;
            max-width: 700px;
            margin-bottom: 2rem;
            animation: fadeInUp 1s ease;
        }
        .button-container {
            display: flex;
            gap: 2rem;
            animation: fadeIn 2s ease;
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
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .btn:hover {
            background-color: #2575fc;
            color: #fff;
            transform: scale(1.05);
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>

    <h1>Welcome to Pathfinder</h1>
    <p>Our mission is to empower low-income and homeless individuals by connecting them to meaningful work opportunities. Start with odd jobs, build credibility, and step into a brighter future.</p>
    <div class="button-container">
        <a href="join_employee.php"><button class="btn">Join as Employee</button></a>
        <a href="employersignin.php"><button class="btn">Join as Employer</button></a>
    </div>

</body>
</html>
