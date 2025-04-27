<?php
session_start();

// Redirect if not logged in\
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

try {
    // Database connection
    $pdo = new PDO('mysql:host=localhost;dbname=pathfinder', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle profile-pic upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
        $file = $_FILES['profile_pic'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Validate MIME type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif'
            ];
            if (isset($allowed[$mime])) {
                $ext = $allowed[$mime];
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $newName = bin2hex(random_bytes(8)) . ".{$ext}";
                $dest = $uploadDir . $newName;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // Update database record
                    $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
                    $stmt->execute(["uploads/{$newName}", $_SESSION['user_id']]);
                    // Redirect to refresh
                    header('Location: profile.php');
                    exit;
                }
            }
        }
    }

    // 1) Fetch the base user record
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, profile_pic, role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) throw new Exception("User not found");

    // Prepare containers
    $extra = [];
    $listings = [];

    if ($user['role'] === 'employee') {
        // 2a) Employee-specific info
        $stmt = $pdo->prepare("SELECT employee_id, rating, verified FROM employees WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $extra = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3a) Recent jobs done
        $stmt = $pdo->prepare("SELECT title, listed_date FROM listings WHERE employee_id = ? ORDER BY listed_date DESC LIMIT 5");
        $stmt->execute([$extra['employee_id']]);
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // 2b) Employer-specific info
        $stmt = $pdo->prepare("SELECT employer_id FROM employers WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $extra = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3b) Job posts
        $stmt = $pdo->prepare("SELECT title, listed_date FROM listings WHERE employer_id = ? ORDER BY listed_date DESC LIMIT 5");
        $stmt->execute([$extra['employer_id']]);
        $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    die("Error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Your Profile</title>
  <style>
   body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f7fa;
  color: #333;
  line-height: 1.6;
  margin: 0;
  padding: 20px;
}

body {
  max-width: 800px;
  margin: 0 auto;
  background-color: #fff;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  padding: 30px;
}

h1 {
  color: #4338ca;
  margin-bottom: 25px;
  font-size: 28px;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 15px;
}

.profile-pic {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #fff;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-right: 20px;
  float: left;
  margin-bottom: 20px;
}

p strong {
  color: #6366f1;
}

p:nth-of-type(1) {
  clear: both;
  margin-bottom: 30px;
  font-size: 16px;
}

.section {
  background-color: #f9fafb;
  padding: 20px;
  margin-bottom: 25px;
  border-radius: 8px;
  border-left: 4px solid #6366f1;
}

h2 {
  color: #4f46e5;
  font-size: 22px;
  margin-top: 0;
  margin-bottom: 15px;
  padding-bottom: 8px;
  border-bottom: 1px solid #e5e7eb;
}

ul {
  list-style-type: none;
  padding-left: 0;
}

li {
  padding: 12px 15px;
  background-color: #fff;
  margin-bottom: 10px;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

li small {
  color: #6b7280;
  font-size: 14px;
}

.section p:last-of-type {
  margin-bottom: 0;
}

.section:last-child {
  background-color: #fff;
  border-left: none;
  padding: 0;
  margin-top: 40px;
  text-align: center;
}

.section:last-child a {
  display: inline-block;
  background-color: #ef4444;
  color: white;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 5px;
  font-weight: 500;
  transition: background-color 0.3s;
}

.section:last-child a:hover {
  background-color: #dc2626;
}

.section p:only-child {
  color: #6b7280;
  font-style: italic;
}

@media (max-width: 600px) {
  body {
    padding: 15px;
  }
  
  .profile-pic {
    float: none;
    display: block;
    margin: 0 auto 20px;
  }
  
  h1 {
    text-align: center;
  }
  
  p:nth-of-type(1) {
    text-align: center;
  }
  
  li {
    flex-direction: column;
    align-items: flex-start;
  }
  
  li small {
    margin-top: 5px;
  }
}
  </style>
</head>
<body>
  <h1>Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
  <img
    src="<?= htmlspecialchars($user['profile_pic'] ?: 'default-avatar.png') ?>"
    alt="Profile Picture"
    class="profile-pic"
  >
  <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

  <div class="section">
    <h2>Change Profile Picture</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="file" name="profile_pic" accept="image/*" required>
      <button type="submit">Upload</button>
    </form>
  </div>

  <?php if ($user['role'] === 'employee'): ?>
    <div class="section">
      <h2>Employee Details</h2>
      <p><strong>Rating:</strong> <?= htmlspecialchars($extra['rating'] ?? 'N/A') ?></p>
      <p><strong>Verified:</strong>
        <?= $extra['verified'] ? '✅ Yes' : '❌ No' ?>
      </p>
    </div>
    <div class="section">
      <h2>Your Recent Jobs Done</h2>
      <?php if ($listings): ?>
        <ul>
          <?php foreach ($listings as $job): ?>
            <li>
              <?= htmlspecialchars($job['title']) ?>
              <small>(<?= htmlspecialchars($job['listed_date']) ?>)</small>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>You haven’t completed any jobs yet.</p>
      <?php endif; ?>
    </div>
  <?php else: /* employer */ ?>
    <div class="section">
      <h2>Your Job Posts</h2>
      <?php if ($listings): ?>
        <ul>
          <?php foreach ($listings as $post): ?>
            <li>
              <?= htmlspecialchars($post['title']) ?>
              <small>(<?= htmlspecialchars($post['listed_date']) ?>)</small>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>You haven’t posted any jobs yet.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="section">
    <a href="logout.php">Log out</a>
  </div>
</body>
</html>
