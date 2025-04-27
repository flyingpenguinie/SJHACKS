<?php
// Listings.php
// Displays a list of job listings with details and action buttons.
// Database connection (adjust credentials as needed)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=pathfinder', 'db_user', 'db_pass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch listings
$stmt = $pdo->query(
    "SELECT j.title, j.type, j.estimated_pay, j.location, u.name AS lister_name, u.profile_pic
     FROM listings j
     JOIN users u ON j.user_id = u.id
     ORDER BY j.created_at DESC"
);
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listings</title>
    <style>
        /* Base */
        * { margin:0; padding:0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7fa; color: #333; }
        a { text-decoration: none; }
        /* Container */
        .container { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        /* Header + Actions */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .header h2 { font-size: 1.5rem; color: #4b6cb7; }
        .actions { display: flex; flex-direction: column; gap: 0.75rem; }
        .btn { padding: 0.6rem 1.2rem; border: none; border-radius: 6px;
               font-size: 0.95rem; cursor: pointer; transition: transform 0.2s ease, opacity 0.2s ease; }
        .btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .btn-primary { background: #4b6cb7; color: #fff; }
        .btn-secondary { background: #e0e4e8; color: #333; }
        /* Listing Cards */
        .listing-card { display: flex; justify-content: space-between; align-items: center;
                         background: #fff; padding: 1rem; border-radius: 8px;
                         box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 1rem; }
        .listing-info { display: flex; align-items: center; gap: 1rem; flex: 1; }
        .avatar { flex-shrink: 0; width: 50px; height: 50px; border-radius: 50%; overflow: hidden;
                  border: 2px solid #4b6cb7; }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .details { display: flex; flex-direction: column; }
        .title { font-size: 1.1rem; font-weight: bold; color: #182848; }
        .meta { font-size: 0.9rem; color: #555; margin-top: 0.2rem; }
        .stats { display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem; min-width: 120px; }
        .stats .pay { font-weight: bold; color: #4b6cb7; }
        .stats .location { font-size: 0.85rem; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Job Listings</h2>
            <div class="actions">
                <button class="btn btn-primary" onclick="location.href='CreateListing.php'">Create Listing</button>
                <button class="btn btn-secondary" onclick="location.href='MyListings.php'">My Listings</button>
            </div>
        </div>

        <?php if (count($listings) > 0): ?>
            <?php foreach ($listings as $listing): ?>
            <div class="listing-card">
                <div class="listing-info">
                    <div class="avatar">
                        <img src="<?= htmlspecialchars($listing['profile_pic']) ?>" alt="<?= htmlspecialchars($listing['lister_name']) ?>">
                    </div>
                    <div class="details">
                        <div class="title"><?= htmlspecialchars($listing['title']) ?></div>
                        <div class="meta"><?= htmlspecialchars($listing['type']) ?> &middot; Posted by <?= htmlspecialchars($listing['lister_name']) ?></div>
                    </div>
                </div>
                <div class="stats">
                    <div class="pay">$<?= htmlspecialchars(number_format($listing['estimated_pay'], 2)) ?> / hr</div>
                    <div class="location"><?= htmlspecialchars($listing['location']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No listings found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
