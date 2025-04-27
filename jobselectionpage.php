<?php
// JobTypeSelection.php
// A page to select job type before proceeding to the job search page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PathFinder - Choose Job Type</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb; /* Dark blue theme */
            --primary-light: #3b82f6;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --light-bg: #f9fafb;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Main Content */
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .title h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        /* Row-based cards container */
        .cards-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }
        
        .card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            display: flex;
            text-decoration: none;
            color: var(--text-color);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .card-icon {
            width: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            background-color: var(--primary-color);
        }
        
        .card-content {
            flex: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .card-header {
            margin-bottom: 0.5rem;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .card-description {
            color: var(--light-text);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        
        .card-action {
            align-self: flex-end;
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.2s;
        }
        
        .card:hover .card-action {
            background-color: var(--primary-light);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .card {
                flex-direction: column;
            }
            
            .card-icon {
                width: 100%;
                height: 100px;
            }
            
            .title h1 {
                font-size: 2rem;
            }
            
            .card-action {
                align-self: center;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">
            <h1>What type of jobs are you looking for?</h1>
        </div>
        
        <div class="cards-container">
            <a href="JobSearch.php?job_type=full%20time&search=true" class="card">
                <div class="card-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="card-content">
                    <div>
                        <h3 class="card-title">Full Time Jobs</h3>
                        <p class="card-description">
                            Permanent positions with regular working hours, benefits, and long-term career growth opportunities. Much more likely to be seen if you have a high Trust Score.
                        </p>
                    </div>
                    <div class="card-action">
                        View Full Time Jobs <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            
            <a href="JobSearch.php?job_type=odd%20jobs&search=true" class="card">
                <div class="card-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="card-content">
                    <div>
                        <h3 class="card-title">Odd Jobs</h3>
                        <p class="card-description">
                            Its hard getting a full time or even part time job with no history or reputation, do odd jobs to maximize your Trust Score.
                        </p>
                    </div>
                    <div class="card-action">
                        View Odd Jobs <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            
            <a href="JobSearch.php" class="card">
                <div class="card-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div class="card-content">
                    <div>
                        <h3 class="card-title">All Job Types</h3>
                        <p class="card-description">
                            See all available opportunities including full-time positions, part-time roles, and odd jobs. Browse the complete job marketplace to explore all possibilities.
                        </p>
                    </div>
                    <div class="card-action">
                        View All Jobs <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>
</body>
</html>