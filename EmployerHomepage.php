<?php
// EmployerHomepage.php
// Employer homepage for Pathfinder with header, profile dropdown, centered title, and tabbed previews.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EmployerHomepage.php</title>
    <style>
        /* Reset & Base */
        * { margin:0; padding:0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7fa; color: #333; }
        a { text-decoration: none; color: inherit; }
        /* Header */
        header {
            position: relative;
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: #fff;
        }
        /* Centered title + subtitle */
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            font-size: 1.75rem;
        }
        .header-text .subtitle {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.85);
            margin-top: 0.25rem;
        }
        /* Profile */
        .profile {
            position: absolute;
            top: 1.5rem;
            right: 2rem;
        }
        .profile img { width:48px; height:48px; border-radius:50%; cursor: pointer; border: 2px solid #fff; }
        .dropdown { display: none; position: absolute; right:0; top: calc(100% + 0.5rem);
                    background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    overflow: hidden; width: 160px; }
        .dropdown a { display: block; padding: 0.75rem 1rem; font-size: 0.95rem; color:#333; }
        .dropdown a:hover { background: #f4f4f4; }
        /* Tabs */
        .tabs { display: flex; justify-content: center; gap: 1rem; margin: 1.5rem 0; }
        .tab { padding: 0.75rem 1.5rem; background: #e0e4e8; border-radius: 8px; cursor: pointer;
               transition: background 0.2s ease, transform 0.2s ease; }
        .tab:hover { transform: translateY(-2px); }
        .tab.active { background: #4b6cb7; color: #fff; }
        /* Previews */
        .preview { display: none; }
        .preview.active { display: block; }
        .preview iframe { width: 100%; height: 400px; border: none; border-radius: 6px;
                          box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        main { padding: 0 2rem 2rem; }
    </style>
</head>
<body>
    <header>
        <div class="header-text">
            <h1>Welcome to Pathfinder</h1>
            <p class="subtitle">Your gateway to community, listings, and messages.</p>
        </div>
        <div class="profile">
            <img src="path/to/profile.jpg" alt="Profile" id="profilePic">
            <div class="dropdown" id="profileDropdown">
                <a href="profileSettings.php">Profile Settings</a>
                <a href="accountSettings.php">Account Settings</a>
                <a href="logout.php">Log Out</a>
            </div>
        </div>
    </header>
    <main>
        <div class="tabs">
            <div class="tab active" data-target="community">Community</div>
            <div class="tab" data-target="listings">Listings</div>
            <div class="tab" data-target="messages">Messages</div>
        </div>
        <div id="community" class="preview active">
            <iframe src="Community.php" title="Community Preview"></iframe>
        </div>
        <div id="listings" class="preview">
            <iframe src="listings.php" title="Listings Preview"></iframe>
        </div>
        <div id="messages" class="preview">
            <iframe src="messages.php" title="Messages Preview"></iframe>
        </div>
    </main>
    <script>
        // Toggle profile dropdown
        const pic = document.getElementById('profilePic');
        const dropdown = document.getElementById('profileDropdown');
        pic.addEventListener('click', () => {
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', (e) => {
            if (!pic.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                const target = tab.getAttribute('data-target');
                document.querySelectorAll('.preview').forEach(p => p.classList.remove('active'));
                document.getElementById(target).classList.add('active');
            });
        });
    </script>
</body>
</html>
