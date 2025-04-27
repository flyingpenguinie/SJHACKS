<?php
// Database connection (MySQLi)
$mysqli = new mysqli("localhost", "root", "", "pathfinder");

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Initialize variables for search queries
$job_search = '';
$event_search = '';
$search = '';

// Handle job and event search
if (isset($_POST['search_jobs'])) {
    $job_search = $_POST['job_search'];
}
if (isset($_POST['search_events'])) {
    $event_search = $_POST['event_search'];
}

// SQL queries for jobs and events based on search input
$job_query = "SELECT * FROM listings WHERE title LIKE '%$job_search%' OR description LIKE '%$job_search%' OR location LIKE '%$job_search%'";
$event_query = "SELECT * FROM events WHERE event_title LIKE '%$event_search%' OR event_description LIKE '%$event_search%' OR event_location LIKE '%$event_search%'";

// Fetch job and event data
$job_result = $mysqli->query($job_query);
$event_result = $mysqli->query($event_query);

// Handle user search (PDO)
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pathfinder', 'root', '');
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE first_name LIKE ? OR last_name LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users");
}
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Community - Path to Employment</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6">

<!-- Search Bar for Jobs -->
<section class="bg-white rounded-2xl shadow-lg p-6 mb-8">
  <h2 class="text-2xl font-semibold text-gray-800 mb-4">Search Jobs</h2>
  <form method="POST" class="flex mb-6">
    <input type="text" name="job_search" value="<?= htmlspecialchars($job_search) ?>" placeholder="Search for jobs" class="w-full p-3 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" name="search_jobs" class="bg-blue-600 text-white px-4 rounded-r-xl hover:bg-blue-700">Search Jobs</button>
  </form>

  <!-- Display Jobs -->
  <h3 class="text-xl font-semibold text-gray-800">Job Listings</h3>
  <ul>
    <?php
    if ($job_result->num_rows > 0) {
        while ($job = $job_result->fetch_assoc()) {
            echo "<li class='bg-gray-50 rounded-xl p-4 mb-4 shadow hover:shadow-lg transition'>";
            echo "<strong class='text-blue-600'>{$job['title']}</strong><br>";
            echo "Location: {$job['location']}<br>";
            echo "Description: {$job['description']}<br>";
            echo "</li>";
        }
    } else {
        echo "No jobs found.";
    }
    ?>
  </ul>
</section>

<!-- Search Bar for Events -->
<section class="bg-white rounded-2xl shadow-lg p-6 mb-8">
  <h2 class="text-2xl font-semibold text-gray-800 mb-4">Search Events</h2>
  <form method="POST" class="flex mb-6">
    <input type="text" name="event_search" value="<?= htmlspecialchars($event_search) ?>" placeholder="Search for events" class="w-full p-3 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" name="search_events" class="bg-blue-600 text-white px-4 rounded-r-xl hover:bg-blue-700">Search Events</button>
  </form>

  <!-- Display Events -->
  <h3 class="text-xl font-semibold text-gray-800">Upcoming Events</h3>
  <ul>
    <?php
    if ($event_result->num_rows > 0) {
        while ($event = $event_result->fetch_assoc()) {
            echo "<li class='bg-gray-50 rounded-xl p-4 mb-4 shadow hover:shadow-lg transition'>";
            echo "<strong class='text-blue-600'>{$event['event_title']}</strong><br>";
            echo "Location: {$event['event_location']}<br>";
            echo "Description: {$event['event_description']}<br>";
            echo "Date: " . date("F j, Y, g:i a", strtotime($event['event_date'])) . "<br>";
            echo "</li>";
        }
    } else {
        echo "No events found.";
    }
    ?>
  </ul>
</section>

<!-- Search Bar for People -->
<section class="bg-white rounded-2xl shadow-lg p-6 mb-8">
  <h2 class="text-2xl font-semibold text-gray-800 mb-4">Search People</h2>
  <form method="get" class="flex mb-6">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by first or last name..." class="w-full p-3 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" class="bg-blue-600 text-white px-4 rounded-r-xl hover:bg-blue-700">Search</button>
  </form>

  <!-- Display People -->
  <h3 class="text-xl font-semibold text-gray-800">Suggested People</h3>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($users as $user): ?>
    <div class="bg-gray-50 rounded-xl p-4 shadow hover:shadow-lg transition">
      <h3 class="font-bold text-lg text-blue-600"><a href="profile.php?user_id=<?= $user['user_id'] ?>"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></a></h3>
      <p class="text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
      <p class="text-gray-500">Role: <?= htmlspecialchars($user['role']) ?></p>
      <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="w-20 h-20 rounded-full mt-2">
    </div>
    <?php endforeach; ?>
  </div>
</section>

</body>
</html>

<?php
// Close the database connection
$mysqli->close();
?>
