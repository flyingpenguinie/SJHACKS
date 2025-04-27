<?php
// Database connection
$servername = "127.0.0.1";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "pathfinder";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// … your existing connection and POST‐apply block…

if (isset($_POST['apply']) && isset($_POST['employer_name']) && isset($_POST['listing_id'])) {
    $employer_name = $_POST['employer_name'];
    $listing_id    = (int)$_POST['listing_id'];
    
    // 1) record the application
    $insertSql  = "INSERT INTO chat_users (employer_name, listing_id, application_date) VALUES (?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("si", $employer_name, $listing_id);
    $ok1 = $insertStmt->execute();
    $insertStmt->close();
    
    // 2) ALSO insert into messages
    //    (adjust columns to match your messages schema)
    $user = "user";
    $messageText = "Thank you for applying to our position! We've received your application and will review it shortly. Do you have any questions about the role?
";
    $replyText = "Please Check Out my Profile! (Embedded Link to Profile)";
    $msgSql  = "INSERT INTO messages (sender, recipient, message_text, timestamp) VALUES (?, ?, ?, NOW())";
    $msgStmt = $conn->prepare($msgSql);
    $msgStmt->bind_param("sss", $employer_name, $user, $messageText);

    $replySql  = "INSERT INTO messages (sender, recipient, message_text, timestamp) VALUES (?, ?, ?, NOW())";
    $replyStmt = $conn->prepare($msgSql);
    $replyStmt->bind_param("sss", $user, $employer_name, $replyText);

    $ok2 = $msgStmt->execute();
    $msgStmt->close();

    $ok3 = $replyStmt->execute();
    $replyStmt->close();


    // 3) send JSON response
    header('Content-Type: application/json');
    if ($ok1 && $ok2 && $ok3) {
        echo json_encode(['success'=>true, 'message'=>'Applied and message sent.']);
    } else {
        echo json_encode([
          'success'=>false,
          'message'=>'Error: '.
                      ($ok1? '' : $conn->error).
                      ($ok2? '' : $conn->error)
        ]);
    }
    exit;
}


// Handle search filters if submitted
$whereClause = "WHERE hidden = 0";
$params = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    // Title search
    if (!empty($_GET['title'])) {
        $title = '%' . $_GET['title'] . '%';
        $whereClause .= " AND title LIKE ?";
        $params[] = $title;
    }
    
    // Location search
    if (!empty($_GET['location'])) {
        $location = '%' . $_GET['location'] . '%';
        $whereClause .= " AND location LIKE ?";
        $params[] = $location;
    }
    
    // Job type filter
    if (!empty($_GET['job_type']) && $_GET['job_type'] != 'all') {
        $whereClause .= " AND job_type = ?";
        $params[] = $_GET['job_type'];
    }
    
    // Date filter - last X days
    if (!empty($_GET['date_range']) && $_GET['date_range'] != 'all') {
        $days = intval($_GET['date_range']);
        $whereClause .= " AND listed_date >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        $params[] = $days;
    }
}

// Prepare the statement for getting listings
$sql = "SELECT l.listing_id, l.title, l.description, l.location, l.job_type, l.listed_date, 
        CONCAT(u.first_name, ' ', u.last_name) AS employer_name, u.profile_pic
        FROM listings l
        JOIN employers e ON l.employer_id = e.employer_id
        JOIN users u ON e.user_id = u.user_id
        $whereClause
        ORDER BY l.listed_date DESC";

$stmt = $conn->prepare($sql);

// Bind parameters if there are any
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathFinder - Job Search</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --secondary-color: #10b981;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            --disabled-color: #9ca3af;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .search-form {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .search-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            width: 100%;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-btn i {
            margin-right: 0.5rem;
        }
        
        .search-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .job-results {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .job-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .job-header {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .employer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 2px solid var(--border-color);
        }
        
        .job-title {
            flex-grow: 1;
        }
        
        .job-title h2 {
            font-size: 1.25rem;
            margin-bottom: 0.25rem;
        }
        
        .employer-name {
            color: var(--light-text);
            font-size: 0.9rem;
        }
        
        .job-badge {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-left: 0.5rem;
            white-space: nowrap;
        }
        
        .odd-jobs {
            background-color: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }
        
        .part-time {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .full-time {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .job-body {
            padding: 1.5rem;
        }
        
        .job-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: var(--light-text);
        }
        
        .job-detail i {
            width: 20px;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .job-description {
            margin: 1.5rem 0;
            font-size: 0.95rem;
            color: var(--text-color);
            line-height: 1.7;
        }
        
        .job-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background-color: var(--light-bg);
            border-top: 1px solid var(--border-color);
        }
        
        .post-date {
            color: var(--light-text);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        
        .post-date i {
            margin-right: 0.25rem;
            font-size: 0.8rem;
        }
        
        .apply-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
        }
        
        .apply-btn i {
            margin-right: 0.25rem;
        }
        
        .apply-btn:hover {
            background-color: #0da271;
        }
        
        .applied-btn {
            background-color: var(--disabled-color);
            cursor: default;
        }
        
        .applied-btn:hover {
            background-color: var(--disabled-color);
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            color: var(--light-text);
        }
        
        .no-results i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }
        
        .no-results h3 {
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .job-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .employer-avatar {
                margin-bottom: 1rem;
            }
            .job-badge {
                margin-left: 0;
                margin-top: 0.5rem;
            }
        }

        /* Pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
            list-style: none;
        }
        
        .pagination li {
            margin: 0 0.25rem;
        }
        
        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background-color: var(--border-color);
        }
        
        .pagination .active a {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Animation keyframes */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .job-card {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .job-card:nth-child(2) { animation-delay: 0.1s; }
        .job-card:nth-child(3) { animation-delay: 0.2s; }
        .job-card:nth-child(4) { animation-delay: 0.3s; }
        .job-card:nth-child(5) { animation-delay: 0.4s; }
        
        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            animation: slideIn 0.3s, fadeOut 0.5s 2.5s;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body>
    <header>
       
    </header>
    
    <div class="container">
        <form class="search-form" method="GET" action="">
            <h2><i class="fas fa-search"></i> Find Your Next Opportunity</h2>
            <p style="margin-bottom: 1.5rem; color: var(--light-text);">Search through available job listings</p>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="title"><i class="fas fa-briefcase"></i> Job Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Software Developer" 
                           value="<?php echo isset($_GET['title']) ? htmlspecialchars($_GET['title']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                    <input type="text" id="location" name="location" placeholder="e.g. San Francisco" 
                           value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="job_type"><i class="fas fa-clock"></i> Job Type</label>
                    <select id="job_type" name="job_type">
                        <option value="all" <?php echo (!isset($_GET['job_type']) || $_GET['job_type'] == 'all') ? 'selected' : ''; ?>>All Types</option>
                        <option value="full time" <?php echo (isset($_GET['job_type']) && $_GET['job_type'] == 'full time') ? 'selected' : ''; ?>>Full Time</option>
                        <option value="part time" <?php echo (isset($_GET['job_type']) && $_GET['job_type'] == 'part time') ? 'selected' : ''; ?>>Part Time</option>
                        <option value="odd jobs" <?php echo (isset($_GET['job_type']) && $_GET['job_type'] == 'odd jobs') ? 'selected' : ''; ?>>Odd Jobs</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_range"><i class="fas fa-calendar"></i> Posted</label>
                    <select id="date_range" name="date_range">
                        <option value="all" <?php echo (!isset($_GET['date_range']) || $_GET['date_range'] == 'all') ? 'selected' : ''; ?>>Any Time</option>
                        <option value="1" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == '1') ? 'selected' : ''; ?>>Last 24 Hours</option>
                        <option value="7" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == '7') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30" <?php echo (isset($_GET['date_range']) && $_GET['date_range'] == '30') ? 'selected' : ''; ?>>Last 30 Days</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="search" class="search-btn">
                        <i class="fas fa-search"></i> Search Jobs
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Toast notification -->
        <div id="toast" class="toast"></div>
        
        <div class="job-results">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Determine job type badge class
                    $badgeClass = '';
                    switch($row['job_type']) {
                        case 'odd jobs':
                            $badgeClass = 'odd-jobs';
                            break;
                        case 'part time':
                            $badgeClass = 'part-time';
                            break;
                        case 'full time':
                            $badgeClass = 'full-time';
                            break;
                    }
                    
                    // Format the date
                    $listedDate = new DateTime($row['listed_date']);
                    $currentDate = new DateTime();
                    $interval = $currentDate->diff($listedDate);
                    
                    if ($interval->days == 0) {
                        $timeAgo = "Today";
                    } elseif ($interval->days == 1) {
                        $timeAgo = "Yesterday";
                    } else {
                        $timeAgo = $interval->days . " days ago";
                    }
                    
                    // Default profile picture if none exists
                    $profilePic = !empty($row['profile_pic']) ? $row['profile_pic'] : 'default-avatar.png';
                    
                    // Display job card
                    echo '<div class="job-card">';
                    echo '    <div class="job-header">';
                    echo '        <img src="' . htmlspecialchars($profilePic) . '" alt="Employer" class="employer-avatar">';
                    echo '        <div class="job-title">';
                    echo '            <h2>' . htmlspecialchars($row['title']) . '</h2>';
                    echo '            <span class="employer-name">' . htmlspecialchars($row['employer_name']) . '</span>';
                    echo '        </div>';
                    echo '        <span class="job-badge ' . $badgeClass . '">' . ucwords(htmlspecialchars($row['job_type'])) . '</span>';
                    echo '    </div>';
                    echo '    <div class="job-body">';
                    echo '        <div class="job-detail">';
                    echo '            <i class="fas fa-map-marker-alt"></i>';
                    echo '            <span>' . htmlspecialchars($row['location']) . '</span>';
                    echo '        </div>';
                    
                    // Check if description exists and display a snippet
                    if (!empty($row['description'])) {
                        $description = $row['description'];
                        // Limit to 150 characters
                        if (strlen($description) > 150) {
                            $description = substr($description, 0, 150) . '...';
                        }
                        echo '        <div class="job-description">' . htmlspecialchars($description) . '</div>';
                    }
                    
                    echo '    </div>';
                    echo '    <div class="job-footer">';
                    echo '        <div class="post-date">';
                    echo '            <i class="far fa-clock"></i>';
                    echo '            <span>Posted ' . $timeAgo . '</span>';
                    echo '        </div>';
                    echo '        <button class="apply-btn" data-listing-id="' . $row['listing_id'] . '" data-employer="' . htmlspecialchars($row['employer_name']) . '">';
                    echo '            <i class="fas fa-paper-plane"></i>';
                    echo '            Apply';
                    echo '        </button>';
                    echo '    </div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-results">';
                echo '    <i class="fas fa-search"></i>';
                echo '    <h3>No job listings found</h3>';
                echo '    <p>Try adjusting your search criteria or check back later for new opportunities.</p>';
                echo '</div>';
            }
            ?>
        </div>
        
        <!-- Pagination - you can implement this functionality as needed -->
        <?php if ($result->num_rows > 0): ?>
        <ul class="pagination">
            <li><a href="#"><i class="fas fa-chevron-left"></i></a></li>
            <li class="active"><a href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i></a></li>
        </ul>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight search form inputs on focus
            const formInputs = document.querySelectorAll('.form-group input, .form-group select');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.boxShadow = '0 0 0 3px rgba(79, 70, 229, 0.1)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.boxShadow = 'none';
                });
            });
            
            // Handle Apply button clicks
            const applyButtons = document.querySelectorAll('.apply-btn');
            applyButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const btn = this;
                    const listingId = btn.getAttribute('data-listing-id');
                    const employerName = btn.getAttribute('data-employer');
                    
                    // Check if already applied
                    if (btn.classList.contains('applied-btn')) {
                        showToast('You have already applied for this job');
                        return;
                    }
                    
                    // AJAX request to submit application
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.href, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (this.readyState === XMLHttpRequest.DONE) {
                            if (this.status === 200) {
                                try {
                                    const response = JSON.parse(this.responseText);
                                    if (response.success) {
                                        // Update button appearance
                                        btn.classList.add('applied-btn');
                                        btn.innerHTML = '<i class="fas fa-check"></i> Applied';
                                        showToast('Application submitted successfully!');
                                    } else {
                                        showToast('Error: ' + response.message);
                                    }
                                } catch (e) {
                                    showToast('Error processing response');
                                }
                            } else {
                                showToast('Error submitting application');
                            }
                        }
                    };
                    xhr.send('apply=true&employer_name=' + encodeURIComponent(employerName) + 
                             '&listing_id=' + encodeURIComponent(listingId));
                });
            });
            
            // Toast notification function
            function showToast(message) {
                const toast = document.getElementById('toast');
                toast.textContent = message;
                toast.style.display = 'block';
                
                // Hide after 3 seconds
                setTimeout(function() {
                    toast.style.display = 'none';
                }, 3000);
            }
        });
    </script>
</body>
</html>

<?php
// Close connection
$stmt->close();
$conn->close();
?>