<?php
// Start session to manage user login state
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pathfinder";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the logged-in user is an employer
$check_role_sql = "SELECT role FROM users WHERE user_id = ?";
$check_role_stmt = $conn->prepare($check_role_sql);
$check_role_stmt->bind_param("i", $user_id);
$check_role_stmt->execute();
$role_result = $check_role_stmt->get_result();

if ($role_result->num_rows > 0) {
    $role_row = $role_result->fetch_assoc();
    if ($role_row['role'] !== 'employer') {
        // Redirect if not an employer
        $_SESSION['error_message'] = "Access denied. Only employers can manage job listings.";
        header("Location: index.php");
        exit();
    }
} else {
    // User not found
    $_SESSION['error_message'] = "User not found.";
    header("Location: login.php");
    exit();
}

// Get the employer_id based on the user_id
$sql = "SELECT employer_id FROM employers WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $employer_id = $row['employer_id'];
} else {
    // If employer not found, redirect to error page
    $_SESSION['error_message'] = "Employer profile not found.";
    header("Location: index.php");
    exit();
}

// Handle delete request
if (isset($_POST['delete_listing'])) {
    $listing_id = $_POST['listing_id'];
    
    // Verify this listing belongs to the current employer
    $check_sql = "SELECT listing_id FROM listings WHERE listing_id = ? AND employer_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $listing_id, $employer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Delete the listing
        $delete_sql = "DELETE FROM listings WHERE listing_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $listing_id);
        if ($delete_stmt->execute()) {
            $success_message = "Listing deleted successfully!";
        } else {
            $error_message = "Error deleting listing: " . $conn->error;
        }
    } else {
        $error_message = "You don't have permission to delete this listing.";
    }
}

// Handle creating/updating listing
if (isset($_POST['submit_listing'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $job_type = $_POST['job_type'];
    $listing_id = isset($_POST['listing_id']) ? $_POST['listing_id'] : null;
    
    // If updating, verify this listing belongs to the current employer
    if ($listing_id) {
        $check_sql = "SELECT listing_id FROM listings WHERE listing_id = ? AND employer_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $listing_id, $employer_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            $error_message = "You don't have permission to edit this listing.";
            $listing_id = null; // Reset listing_id to prevent update
        }
    }
    
    // Get a valid employee_id from available employees
    // For simplicity, we'll randomly select one from the database
    $emp_sql = "SELECT employee_id FROM employees ORDER BY RAND() LIMIT 1";
    $emp_result = $conn->query($emp_sql);
    
    if ($emp_result->num_rows > 0) {
        $emp_row = $emp_result->fetch_assoc();
        $employee_id = $emp_row['employee_id'];
        
        if ($listing_id) {
            // Update existing listing
            $sql = "UPDATE listings SET title = ?, description = ?, location = ?, job_type = ? WHERE listing_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $description, $location, $job_type, $listing_id);
            
            if ($stmt->execute()) {
                $success_message = "Listing updated successfully!";
            } else {
                $error_message = "Error updating listing: " . $conn->error;
            }
        } else {
            // Create new listing
            $sql = "INSERT INTO listings (title, description, employee_id, employer_id, location, job_type) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiiss", $title, $description, $employee_id, $employer_id, $location, $job_type);
            
            if ($stmt->execute()) {
                $success_message = "Listing created successfully!";
            } else {
                $error_message = "Error creating listing: " . $conn->error;
            }
        }
    } else {
        $error_message = "No employees available in the system. Cannot create listing.";
    }
}

// Fetch listing to edit if edit_id is set
$edit_listing = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    
    // Verify this listing belongs to the current employer
    $edit_sql = "SELECT * FROM listings WHERE listing_id = ? AND employer_id = ?";
    $edit_stmt = $conn->prepare($edit_sql);
    $edit_stmt->bind_param("ii", $edit_id, $employer_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    
    if ($edit_result->num_rows > 0) {
        $edit_listing = $edit_result->fetch_assoc();
    } else {
        $error_message = "You don't have permission to edit this listing.";
    }
}

// Get all listings for this employer
$listings_sql = "SELECT l.*, u.first_name, u.last_name, u.profile_pic FROM listings l
                JOIN employees e ON l.employee_id = e.employee_id
                JOIN users u ON e.user_id = u.user_id
                WHERE l.employer_id = ? ORDER BY l.listed_date DESC";
$listings_stmt = $conn->prepare($listings_sql);
$listings_stmt->bind_param("i", $employer_id);
$listings_stmt->execute();
$listings_result = $listings_stmt->get_result();

// Get employer name for the page header
$employer_name_sql = "SELECT u.first_name, u.last_name FROM users u
                      JOIN employers e ON u.user_id = e.user_id
                      WHERE e.employer_id = ?";
$employer_name_stmt = $conn->prepare($employer_name_sql);
$employer_name_stmt->bind_param("i", $employer_id);
$employer_name_stmt->execute();
$employer_name_result = $employer_name_stmt->get_result();
$employer_name = $employer_name_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(46, 72, 124);
            --secondary-color:rgb(46, 72, 124);
            --accent-color: rgb(46, 72, 124);
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .listing-card {
            border-left: 5px solid var(--primary-color);
        }
        
        .form-card {
            border-top: 5px solid var(--accent-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.7rem;
        }
        
        .badge-full-time {
            background-color: #4cc9f0;
            color: var(--dark-color);
        }
        
        .badge-part-time {
            background-color: #4361ee;
            color: white;
        }
        
        .badge-odd-jobs {
            background-color: #7209b7;
            color: white;
        }
        
        .listing-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .listing-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .employee-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-color);
        }
        
        .action-btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.85rem;
        }
        
        .section-title {
            position: relative;
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 60px;
            background-color: var(--accent-color);
        }
        
        .alert {
            border-radius: 0.5rem;
        }
        
        .no-listings {
            text-align: center;
            padding: 3rem;
            border: 2px dashed #dee2e6;
            border-radius: 1rem;
            margin: 2rem 0;
        }
        
        .no-listings i {
            font-size: 3rem;
            color: #adb5bd;
            margin-bottom: 1rem;
        }
        
        .user-welcome {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }
        
        .user-welcome small {
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .card {
                margin-bottom: 1rem;
            }
            
            .listing-header {
                flex-direction: column;
            }
            
            .listing-badges {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    
    
    <div class="container mb-5">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <br>
                <h2 class="section-title">Your Job Listings</h2>
                
                <?php if ($listings_result->num_rows > 0): ?>
                    <?php while ($listing = $listings_result->fetch_assoc()): ?>
                        <div class="card listing-card mb-4">
                            <div class="card-body">
                                <div class="listing-header mb-3">
                                    <div>
                                        <h3 class="card-title mb-1"><?php echo htmlspecialchars($listing['title']); ?></h3>
                                        <p class="listing-date mb-2">
                                            <i class="far fa-calendar-alt me-1"></i> Posted on <?php echo date('M d, Y', strtotime($listing['listed_date'])); ?>
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo htmlspecialchars($listing['profile_pic']); ?>" alt="Employee" class="employee-avatar me-2">
                                        <div>
                                            
                                            <span class="badge 
                                                <?php 
                                                    switch($listing['job_type']) {
                                                        case 'full time': echo 'badge-full-time'; break;
                                                        case 'part time': echo 'badge-part-time'; break;
                                                        case 'odd jobs': echo 'badge-odd-jobs'; break;
                                                    } 
                                                ?>">
                                                <?php echo ucwords($listing['job_type']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                                
                                <?php if ($listing['location']): ?>
                                    <p class="card-text mb-3">
                                        <i class="fas fa-map-marker-alt me-1"></i> 
                                        <?php echo htmlspecialchars($listing['location']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-flex mt-3">
                                    <a href="?edit_id=<?php echo $listing['listing_id']; ?>" class="btn btn-outline-primary action-btn me-2">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this listing?');">
                                        <input type="hidden" name="listing_id" value="<?php echo $listing['listing_id']; ?>">
                                        <button type="submit" name="delete_listing" class="btn btn-outline-danger action-btn">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-listings">
                        <i class="fas fa-clipboard"></i>
                        <h3>No Job Listings Yet</h3>
                        <p class="text-muted">Create your first job listing using the form.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="card form-card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <?php echo $edit_listing ? 'Edit Listing' : 'Create New Listing'; ?>
                        </h3>
                        
                        <form method="post">
                            <?php if ($edit_listing): ?>
                                <input type="hidden" name="listing_id" value="<?php echo $edit_listing['listing_id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Job Title</label>
                                <input type="text" class="form-control" id="title" name="title" required
                                       value="<?php echo $edit_listing ? htmlspecialchars($edit_listing['title']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Job Description</label>
                                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo $edit_listing ? htmlspecialchars($edit_listing['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location"
                                       value="<?php echo $edit_listing ? htmlspecialchars($edit_listing['location']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="job_type" class="form-label">Job Type</label>
                                <select class="form-select" id="job_type" name="job_type" required>
                                    <option value="full time" <?php echo ($edit_listing && $edit_listing['job_type'] == 'full time') ? 'selected' : ''; ?>>Full Time</option>
                                    <option value="part time" <?php echo ($edit_listing && $edit_listing['job_type'] == 'part time') ? 'selected' : ''; ?>>Part Time</option>
                                    <option value="odd jobs" <?php echo ($edit_listing && $edit_listing['job_type'] == 'odd jobs') ? 'selected' : ''; ?>>Odd Jobs</option>
                                </select>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="submit_listing" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    <?php echo $edit_listing ? 'Update Listing' : 'Create Listing'; ?>
                                </button>
                            </div>
                            
                            <?php if ($edit_listing): ?>
                                <div class="d-grid mt-2">
                                    <a href="listings.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-plus me-1"></i> Create New Instead
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>