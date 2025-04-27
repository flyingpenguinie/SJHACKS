<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=pathfinder', 'root', '');

// Handle search
$search = '';
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

<!-- Search People -->
<section class="bg-white rounded-2xl shadow-lg p-6 mb-8">
  <h2 class="text-2xl font-semibold text-gray-800 mb-4">Search People</h2>
  <form method="get" class="flex">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by first or last name..." class="w-full p-3 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" class="bg-blue-600 text-white px-4 rounded-r-xl hover:bg-blue-700">Search</button>
  </form>
</section>

<!-- Suggested People -->
<section class="bg-white rounded-2xl shadow-lg p-6 mb-8">
  <h2 class="text-2xl font-semibold text-gray-800 mb-4">People</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($users as $user): ?>
    <div class="bg-gray-50 rounded-xl p-4 shadow hover:shadow-lg transition">
      <h3 class="font-bold text-lg text-blue-600"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
      <p class="text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
      <p class="text-gray-500">Role: <?= htmlspecialchars($user['role']) ?></p>
      <img src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="w-20 h-20 rounded-full mt-2">
    </div>
    <?php endforeach; ?>
  </div>
</section>

</body>
</html>
