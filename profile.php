<?php
// Include the database connection
include('db.php');

// Check if the user_id is passed in the URL
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Query to fetch the user details based on the user_id
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit;
    }
} else {
    echo "No user ID provided.";
    exit;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></title>
</head>
<body>
    <h1>Profile of <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></h1>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>Role: <?= htmlspecialchars($user['role']) ?></p>
    <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="profile-pic">
    <!-- Add more details as needed -->
</body>
</html>
