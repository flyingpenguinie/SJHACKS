<?php
// Enhanced Chat Application with database storage and conversation switching
// Database connection (adjust credentials as needed)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=pathfinder', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize session to track current conversation
session_start();

// Handle AJAX requests
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

// Handle adding new contact
// In the PHP section where you handle adding a new contact, modify the code:
    if ($action === 'add_contact') {
        $newContact = $_POST['contact'] ?? '';
        
        if (!empty($newContact)) {
            try {
                // First check if contact already exists
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM chat_users WHERE employer_name = :name");
                $checkStmt->execute(['name' => $newContact]);
                
                if ($checkStmt->fetchColumn() == 0) {
                    // Add new contact
                    $stmt = $pdo->prepare("INSERT INTO chat_users (employer_name) VALUES (:name)");
                    $stmt->execute(['name' => $newContact]);
                    
                    // Add default "Hello user" message from this contact
                    $defaultMsg = "Hello user";
                    $timestamp = date('Y-m-d H:i:s');
                    
                    $msgStmt = $pdo->prepare("INSERT INTO messages (sender, recipient, message_text, timestamp) 
                                            VALUES (:sender, :recipient, :message, :timestamp)");
                    $msgStmt->execute([
                        'sender' => $newContact,
                        'recipient' => 'user',
                        'message' => $defaultMsg,
                        'timestamp' => $timestamp
                    ]);
                    
                    // Add automatic response when user clicks "Apply"
                    $welcomeMsg = "Thank you for applying to our position! We've received your application and will review it shortly. Do you have any questions about the role?";
                    $welcomeTimestamp = date('Y-m-d H:i:s', strtotime('+2 seconds')); // Slight delay to make it feel natural
                    
                    $welcomeStmt = $pdo->prepare("INSERT INTO messages (sender, recipient, message_text, timestamp) 
                                             VALUES (:sender, :recipient, :message, :timestamp)");
                    $welcomeStmt->execute([
                        'sender' => $newContact,
                        'recipient' => 'user',
                        'message' => $welcomeMsg,
                        'timestamp' => $welcomeTimestamp
                    ]);
                    
                    // Return contact info for dynamic addition along with welcome message
                    echo json_encode([
                        'status' => 'success',
                        'contact' => [
                            'name' => $newContact,
                            'initial' => strtoupper(substr($newContact, 0, 1)),
                            'preview' => $welcomeMsg,
                            'time' => date('h:i A', strtotime($welcomeTimestamp))
                        ],
                        'welcomeMessage' => [
                            'sender' => $newContact,
                            'message' => $welcomeMsg,
                            'timestamp' => date('h:i A', strtotime($welcomeTimestamp))
                        ]
                    ]);
                    exit;
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Contact already exists']);
                    exit;
                }
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                exit;
            }
        }
        
        echo json_encode(['status' => 'error', 'message' => 'Invalid contact name']);
        exit;
    }

// Handle message sending via AJAX
if ($action === 'send_message') {
    $sender = $_POST['sender'] ?? 'user';
    $recipient = $_POST['recipient'] ?? '';
    $message = $_POST['message'] ?? '';
    $timestamp = date('Y-m-d H:i:s');
    
    if (!empty($message) && !empty($recipient)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (sender, recipient, message_text, timestamp) 
                                 VALUES (:sender, :recipient, :message, :timestamp)");
            $stmt->execute([
                'sender' => $sender,
                'recipient' => $recipient,
                'message' => $message,
                'timestamp' => $timestamp
            ]);
            
            echo json_encode([
                'status' => 'success',
                'message' => $message,
                'timestamp' => date('h:i A', strtotime($timestamp))
            ]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Invalid message data']);
    exit;
}

// Handle fetching messages for a specific conversation
if ($action === 'get_messages') {
    $currentUser = $_GET['user'] ?? 'user';
    $recipient = $_GET['recipient'] ?? '';
    
    if (!empty($recipient)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM messages 
                                 WHERE (sender = :user AND recipient = :recipient)
                                 OR (sender = :recipient AND recipient = :user)
                                 ORDER BY timestamp ASC");
            $stmt->execute([
                'user' => $currentUser,
                'recipient' => $recipient
            ]);
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'messages' => $messages]);
            exit;
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Set current recipient
$current_recipient = $_GET['recipient'] ?? '';

// Fetch all available chat users
$stmt = $pdo->query("SELECT employer_name AS users FROM chat_users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get preview messages for each user
foreach ($users as $key => $user) {
    $stmt = $pdo->prepare("SELECT message_text, timestamp FROM messages 
                         WHERE (sender = :user AND recipient = 'user')
                         OR (sender = 'user' AND recipient = :user)
                         ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute(['user' => $user['users']]);
    $preview = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($preview) {
        $users[$key]['preview'] = substr($preview['message_text'], 0, 25) . (strlen($preview['message_text']) > 25 ? '...' : '');
        $users[$key]['time'] = date('h:i A', strtotime($preview['timestamp']));
    } else {
        $users[$key]['preview'] = 'No messages yet';
        $users[$key]['time'] = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Enhanced Chat UI</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* === Reset and Base Styles === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #333;
            background: #f5f7fb;
        }

        /* === App Layout === */
        .app {
            display: flex;
            height: 100%;
            max-width: 1400px;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background: #fff;
        }

        /* === Sidebar Styles === */
        .sidebar {
            width: 320px;
            border-right: 1px solid #eaeaea;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .sidebar-header {
            padding: 1.2rem;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eaeaea;
            color: #3a5f9e;
        }

        .sidebar-header button {
            width: 36px;
            height: 36px;
            font-size: 1rem;
            background: #3a5f9e;
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .sidebar-header button:hover {
            background: #2d4b7d;
            transform: scale(1.05);
        }

        .search-bar {
            padding: 0.8rem;
            border-bottom: 1px solid #eaeaea;
        }

        .search-bar input {
            width: 100%;
            padding: 0.7rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            outline: none;
            font-size: 0.9rem;
            background: #f5f5f5;
            transition: all 0.2s;
        }

        .search-bar input:focus {
            border-color: #3a5f9e;
            background: #fff;
            box-shadow: 0 0 0 2px rgba(58, 95, 158, 0.1);
        }

        .convo-list {
            flex: 1;
            overflow-y: auto;
        }

        .convo-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #f5f5f5;
            position: relative;
        }

        .convo-item:hover {
            background: #f8f9fb;
        }

        .convo-item.active {
            background: #edf3ff;
            border-left: 3px solid #3a5f9e;
        }

        .convo-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(45deg, #3a5f9e, #6b8cbe);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
            margin-right: 0.9rem;
        }

        .convo-info {
            flex: 1;
        }

        .convo-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: #333;
        }

        .convo-preview {
            font-size: 0.85rem;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        .convo-time {
            font-size: 0.75rem;
            color: #999;
            margin-left: 0.5rem;
        }

        .unread-badge {
            width: 20px;
            height: 20px;
            background: #3a5f9e;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            font-weight: 600;
        }

        /* === Chat Area Styles === */
        .chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f5f7fb;
        }

        .chat-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #eaeaea;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .chat-header .title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 1.1rem;
        }

        .chat-header .title .status {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #4CAF50;
            border-radius: 50%;
            margin-left: 5px;
        }

        .chat-header .title .username {
            font-weight: 600;
            color: #3a5f9e;
        }

        .chat-header .actions {
            display: flex;
            gap: 0.5rem;
        }

        .chat-header .actions button {
            width: 36px;
            height: 36px;
            background: #f0f4fa;
            border: none;
            border-radius: 50%;
            color: #3a5f9e;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chat-header .actions button:hover {
            background: #e0e7f5;
        }

        .messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .message-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
            max-width: 80%;
        }

        .message-group.mine {
            align-self: flex-end;
        }

        .message-group.theirs {
            align-self: flex-start;
        }

        .message {
            padding: 0.8rem 1rem;
            border-radius: 18px;
            position: relative;
            line-height: 1.5;
            margin-bottom: 0.2rem;
            font-size: 0.95rem;
            word-break: break-word;
        }

        .message-group.theirs .message {
            background: #fff;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .message-group.mine .message {
            background: #3a5f9e;
            color: #fff;
            border-bottom-right-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .message .timestamp {
            display: block;
            font-size: 0.7rem;
            margin-top: 0.4rem;
            opacity: 0.7;
            text-align: right;
        }

        .chat-input {
            padding: 1rem 1.5rem;
            border-top: 1px solid #eaeaea;
            display: flex;
            gap: 0.8rem;
            background: #fff;
            align-items: center;
        }

        .chat-input .attachments {
            display: flex;
            gap: 0.5rem;
        }

        .chat-input .attachments button {
            width: 40px;
            height: 40px;
            border: none;
            background: #f0f4fa;
            border-radius: 50%;
            color: #3a5f9e;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chat-input .attachments button:hover {
            background: #e0e7f5;
        }

        .chat-input input {
            flex: 1;
            padding: 0.8rem 1.2rem;
            border: 1px solid #e0e0e0;
            border-radius: 24px;
            outline: none;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .chat-input input:focus {
            border-color: #3a5f9e;
            box-shadow: 0 0 0 2px rgba(58, 95, 158, 0.1);
        }

        .chat-input .send-btn {
            width: 48px;
            height: 48px;
            border: none;
            background: #3a5f9e;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chat-input .send-btn:hover {
            background: #2d4b7d;
            transform: scale(1.05);
        }

        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #888;
            text-align: center;
            padding: 2rem;
        }

        .empty-chat i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ccc;
        }

        .empty-chat h3 {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        /* Loading animation */
        .loading {
            display: flex;
            justify-content: center;
            padding: 2rem;
        }

        .loading div {
            width: 12px;
            height: 12px;
            margin: 0 5px;
            background: #3a5f9e;
            border-radius: 50%;
            animation: bounce 1s infinite alternate;
        }

        .loading div:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading div:nth-child(3) {
            animation-delay: 0.4s;
        }

        /* New Contact Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 90%;
            max-width: 400px;
            padding: 2rem;
        }

        .modal-content h3 {
            margin-bottom: 1.5rem;
            color: #3a5f9e;
            font-weight: 600;
        }

        .modal-content .input-group {
            margin-bottom: 1.5rem;
        }

        .modal-content .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .modal-content .input-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .modal-content .input-group input:focus {
            border-color: #3a5f9e;
            outline: none;
            box-shadow: 0 0 0 2px rgba(58, 95, 158, 0.1);
        }

        .modal-content .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 0.8rem;
        }

        .modal-content .buttons button {
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-content .buttons .cancel-btn {
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            color: #666;
        }

        .modal-content .buttons .cancel-btn:hover {
            background: #eaeaea;
        }

        .modal-content .buttons .apply-btn {
            background: #3a5f9e;
            border: 1px solid #3a5f9e;
            color: white;
        }

        .modal-content .buttons .apply-btn:hover {
            background: #2d4b7d;
        }

        @keyframes bounce {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-10px);
            }
        }

        /* === Responsive Styles === */
        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                left: -320px;
                height: 100%;
                z-index: 100;
                transition: left 0.3s ease;
            }
            
            .sidebar.open {
                left: 0;
            }
            
            .chat-header .menu-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="app">
        <div class="sidebar">
            <div class="sidebar-header">
                <span>Conversations</span>
                <button id="new-chat-btn" title="New conversation"><i class="fas fa-plus"></i></button>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Search conversations..." id="search-input">
            </div>
            <div class="convo-list" id="convo-list">
                <?php foreach ($users as $u): ?>
                    <div class="convo-item <?= ($current_recipient === $u['users']) ? 'active' : '' ?>" 
                         data-name="<?= htmlspecialchars($u['users']) ?>">
                        <div class="convo-avatar">
                            <?= strtoupper(substr($u['users'], 0, 1)) ?>
                        </div>
                        <div class="convo-info">
                            <div class="convo-name"><?= htmlspecialchars($u['users']) ?></div>
                            <div class="convo-preview"><?= htmlspecialchars($u['preview']) ?></div>
                        </div>
                        <?php if (!empty($u['time'])): ?>
                            <div class="convo-time"><?= htmlspecialchars($u['time']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chat">
            <div class="chat-header">
                <div class="title">
                    <div class="convo-avatar" id="chat-avatar">
                        <?= $current_recipient ? strtoupper(substr($current_recipient, 0, 1)) : '?' ?>
                    </div>
                    <div>
                        <span class="username" id="chat-name"><?= htmlspecialchars($current_recipient ?: 'Select a conversation') ?></span>
                        <?php if ($current_recipient): ?>
                            <span class="status"></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="actions">
                    <button id="call-btn" title="Call"><i class="fas fa-phone"></i></button>
                    <button id="video-btn" title="Video call"><i class="fas fa-video"></i></button>
                    <button id="info-btn" title="Info"><i class="fas fa-info-circle"></i></button>
                </div>
            </div>

            <div class="messages" id="messages">
                <?php if (!$current_recipient): ?>
                    <div class="empty-chat">
                        <i class="far fa-comments"></i>
                        <h3>Start a conversation</h3>
                        <p>Select a contact from the sidebar to start chatting</p>
                    </div>
                <?php endif; ?>
                <!-- Messages will be loaded dynamically -->
            </div>

            <div class="chat-input">
                <div class="attachments">
                    <button title="Attach file"><i class="fas fa-paperclip"></i></button>
                    <button title="Send image"><i class="far fa-image"></i></button>
                </div>
                <input type="text" id="msg-input" placeholder="Type a message…" <?= $current_recipient ? '' : 'disabled' ?>>
                <button class="send-btn" id="send-btn" <?= $current_recipient ? '' : 'disabled' ?>>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- New Contact Modal -->
    <div class="modal" id="contact-modal">
        <div class="modal-content">
            <h3>Add New Contact</h3>
            <div class="input-group">
                <label for="contact-name">Contact Name</label>
                <input type="text" id="contact-name" placeholder="Enter contact name">
            </div>
            <div class="buttons">
                <button class="cancel-btn" id="cancel-btn">Cancel</button>
                <button class="apply-btn" id="apply-btn">Apply</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const msgInput = document.getElementById('msg-input');
        const sendBtn = document.getElementById('send-btn');
        const messagesContainer = document.getElementById('messages');
        const chatName = document.getElementById('chat-name');
        const chatAvatar = document.getElementById('chat-avatar');
        const convoList = document.getElementById('convo-list');
        const searchInput = document.getElementById('search-input');
        const newChatBtn = document.getElementById('new-chat-btn');
        const contactModal = document.getElementById('contact-modal');
        const contactNameInput = document.getElementById('contact-name');
        const cancelBtn = document.getElementById('cancel-btn');
        const applyBtn = document.getElementById('apply-btn');
        
        let currentRecipient = '<?= $current_recipient ?>';
        let convoItems = document.querySelectorAll('.convo-item');
        
        // Open new contact modal
        newChatBtn.addEventListener('click', () => {
            contactModal.classList.add('show');
            contactNameInput.focus();
        });
        
        // Close modal on cancel
        cancelBtn.addEventListener('click', () => {
            contactModal.classList.remove('show');
            contactNameInput.value = '';
        });
        
// Modify the JavaScript section where you handle adding new contacts:

// Add new contact on apply
applyBtn.addEventListener('click', () => {
    const newContactName = contactNameInput.value.trim();
    
    if (newContactName) {
        // Create form data
        const formData = new FormData();
        formData.append('action', 'add_contact');
        formData.append('contact', newContactName);
        
        // Add contact via AJAX
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Create new contact element
                const newContact = document.createElement('div');
                newContact.className = 'convo-item';
                newContact.setAttribute('data-name', data.contact.name);
                
                newContact.innerHTML = `
                    <div class="convo-avatar">
                        ${data.contact.initial}
                    </div>
                    <div class="convo-info">
                        <div class="convo-name">${data.contact.name}</div>
                        <div class="convo-preview">${data.contact.preview}</div>
                    </div>
                    <div class="convo-time">${data.contact.time}</div>
                `;
                
                // Add to the top of the list
                convoList.prepend(newContact);
                
                // Add click event to new contact
                newContact.addEventListener('click', () => {
                    selectConversation(data.contact.name, newContact);
                });
                
                // Update convo items list
                convoItems = document.querySelectorAll('.convo-item');
                
                // Close modal
                contactModal.classList.remove('show');
                contactNameInput.value = '';
                
                // Switch to the new conversation
                selectConversation(data.contact.name, newContact);
                
                // Clear existing messages
                messagesContainer.innerHTML = '';
                
                // Display all messages in the proper order
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        // Determine if this is user's message or contact's message
                        const isMyMessage = msg.sender === 'user';
                        
                        // Create message group
                        const messageGroup = document.createElement('div');
                        messageGroup.className = `message-group ${isMyMessage ? 'mine' : 'theirs'}`;
                        messagesContainer.appendChild(messageGroup);
                        
                        // Create and add message
                        const messageEl = document.createElement('div');
                        messageEl.className = 'message';
                        messageEl.innerHTML = `
                            ${msg.message}
                            <span class="timestamp">${msg.timestamp}</span>
                        `;
                        
                        messageGroup.appendChild(messageEl);
                    });
                    
                    // Scroll to bottom to show all messages
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            } else {
                alert('Error adding contact: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error adding contact: ' + error.message);
        });
    }
});
        
        // Handle Enter key in the modal
        contactNameInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyBtn.click();
            }
        });
        
        // Filter conversations when searching
        searchInput.addEventListener('input', () => {
            const searchTerm = searchInput.value.toLowerCase();
            convoItems.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                if (name.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Select conversation function
        function selectConversation(recipient, item) {
            // Update UI
            convoItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');
            
            chatName.textContent = recipient;
            chatAvatar.textContent = recipient.charAt(0).toUpperCase();
            
            // Enable input
            msgInput.disabled = false;
            sendBtn.disabled = false;
            
            // Update URL without reloading
            history.pushState({}, '', `?recipient=${encodeURIComponent(recipient)}`);
            
            // Set current recipient and load messages
            currentRecipient = recipient;
            loadMessages(recipient);
        }
        
        // Load messages for selected conversation
        function loadMessages(recipient) {
            messagesContainer.innerHTML = `
                <div class="loading">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            `;
            
            fetch(`?action=get_messages&recipient=${encodeURIComponent(recipient)}&user=user`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        messagesContainer.innerHTML = '';
                        
                        if (data.messages.length === 0) {
                            messagesContainer.innerHTML = `
                                <div class="empty-chat">
                                    <i class="far fa-comments">
                                    <i class="far fa-comments"></i>
                                    <h3>No messages yet</h3>
                                    <p>Start a conversation with ${recipient}</p>
                                </div>
                            `;
                            return;
                        }
                        
                        let currentSender = null;
                        let messageGroup = null;
                        
                        data.messages.forEach(msg => {
                            const isMyMessage = msg.sender === 'user';
                            const messageTime = new Date(msg.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            
                            // Create a new message group if sender changes
                            if (currentSender !== msg.sender) {
                                currentSender = msg.sender;
                                messageGroup = document.createElement('div');
                                messageGroup.className = `message-group ${isMyMessage ? 'mine' : 'theirs'}`;
                                messagesContainer.appendChild(messageGroup);
                            }
                            
                            const messageEl = document.createElement('div');
                            messageEl.className = 'message';
                            messageEl.innerHTML = `
                                ${msg.message_text}
                                <span class="timestamp">${messageTime}</span>
                            `;
                            
                            messageGroup.appendChild(messageEl);
                        });
                        
                        // Scroll to bottom
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    } else {
                        messagesContainer.innerHTML = `
                            <div class="empty-chat">
                                <i class="fas fa-exclamation-circle"></i>
                                <h3>Error loading messages</h3>
                                <p>${data.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    messagesContainer.innerHTML = `
                        <div class="empty-chat">
                            <i class="fas fa-exclamation-circle"></i>
                            <h3>Error loading messages</h3>
                            <p>${error.message}</p>
                        </div>
                    `;
                });
        }
        
        // Switch conversation
        convoItems.forEach(item => {
            item.addEventListener('click', () => {
                const recipient = item.getAttribute('data-name');
                selectConversation(recipient, item);
            });
        });
        
        // Send message function
        function sendMessage() {
            const text = msgInput.value.trim();
            if (!text || !currentRecipient) return;
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('sender', 'user');
            formData.append('recipient', currentRecipient);
            formData.append('message', text);
            
            // Send the message
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Add message to UI
                    let messageGroup = messagesContainer.querySelector('.message-group.mine:last-child');
                    
                    // Create new message group if needed
                    if (!messageGroup || messageGroup.previousElementSibling && 
                        messageGroup.previousElementSibling.classList.contains('mine')) {
                        messageGroup = document.createElement('div');
                        messageGroup.className = 'message-group mine';
                        messagesContainer.appendChild(messageGroup);
                    }
                    
                    const messageEl = document.createElement('div');
                    messageEl.className = 'message';
                    messageEl.innerHTML = `
                        ${data.message}
                        <span class="timestamp">${data.timestamp}</span>
                    `;
                    
                    messageGroup.appendChild(messageEl);
                    
                    // Update conversation preview
                    const convoItem = document.querySelector(`.convo-item[data-name="${currentRecipient}"]`);
                    if (convoItem) {
                        const previewEl = convoItem.querySelector('.convo-preview');
                        const timeEl = convoItem.querySelector('.convo-time');
                        
                        if (previewEl) {
                            previewEl.textContent = text.length > 25 ? text.substring(0, 25) + '...' : text;
                        }
                        
                        if (timeEl) {
                            timeEl.textContent = data.timestamp;
                        }
                    }
                    
                    // Clear input and scroll down
                    msgInput.value = '';
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                } else {
                    alert('Error sending message: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error sending message: ' + error.message);
            });
        }
        
        // Event listeners
        sendBtn.addEventListener('click', sendMessage);
        
        msgInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // Load initial messages if recipient is selected
        if (currentRecipient) {
            loadMessages(currentRecipient);
        }
    });
    </script>
</body>
</html>