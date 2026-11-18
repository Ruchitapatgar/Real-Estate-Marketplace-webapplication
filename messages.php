<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle sending new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $property_id = intval($_POST['property_id']);
    $message_text = sanitize($_POST['message_text']);
    
    if (!empty($message_text)) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, property_id, message_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $_SESSION['user_id'], $receiver_id, $property_id, $message_text);
        
        if ($stmt->execute()) {
            $success = 'Message sent successfully!';
        } else {
            $error = 'Failed to send message.';
        }
        $stmt->close();
    }
}

// Mark messages as read when viewing
if (isset($_GET['conversation_id']) && isset($_GET['property_id'])) {
    $conversation_id = intval($_GET['conversation_id']);
    $property_id = intval($_GET['property_id']);
    
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND property_id = ?");
    $stmt->bind_param("iii", $_SESSION['user_id'], $conversation_id, $property_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch unique conversations
$conversations_query = "
    SELECT DISTINCT
        CASE 
            WHEN sender_id = ? THEN receiver_id 
            ELSE sender_id 
        END as other_user_id,
        property_id,
        (SELECT name FROM users WHERE id = 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END
        ) as other_user_name,
        (SELECT title FROM properties WHERE id = property_id) as property_title,
        (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND sender_id = 
            CASE 
                WHEN messages.sender_id = ? THEN messages.sender_id 
                ELSE messages.receiver_id 
            END
            AND property_id = messages.property_id AND is_read = 0
        ) as unread_count,
        MAX(timestamp) as last_message_time
    FROM messages
    WHERE sender_id = ? OR receiver_id = ?
    GROUP BY other_user_id, property_id
    ORDER BY last_message_time DESC
";

$stmt = $conn->prepare($conversations_query);
$uid = $_SESSION['user_id'];
$stmt->bind_param("iiiiii", $uid, $uid, $uid, $uid, $uid, $uid);
$stmt->execute();
$conversations = $stmt->get_result();

// If viewing a specific conversation
$messages = null;
$current_conversation = null;
if (isset($_GET['conversation_id']) && isset($_GET['property_id'])) {
    $conversation_id = intval($_GET['conversation_id']);
    $property_id = intval($_GET['property_id']);
    
    // Get conversation details
    $conv_stmt = $conn->prepare("
        SELECT u.name, p.title, p.id as property_id
        FROM users u, properties p
        WHERE u.id = ? AND p.id = ?
    ");
    $conv_stmt->bind_param("ii", $conversation_id, $property_id);
    $conv_stmt->execute();
    $current_conversation = $conv_stmt->get_result()->fetch_assoc();
    
    // Fetch messages
    $messages_stmt = $conn->prepare("
        SELECT m.*, u.name as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE property_id = ? 
        AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        ORDER BY timestamp ASC
    ");
    $messages_stmt->bind_param("iiiii", $property_id, $_SESSION['user_id'], $conversation_id, $conversation_id, $_SESSION['user_id']);
    $messages_stmt->execute();
    $messages = $messages_stmt->get_result();
}

// Handle new conversation from property page
$new_receiver_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : null;
$new_property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .messages-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        .conversations-list {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1rem;
            box-shadow: var(--shadow);
            height: 600px;
            overflow-y: auto;
        }
        .conversation-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.3s ease;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }
        .conversation-item:hover {
            background: var(--bg-color);
        }
        .conversation-item.active {
            background: var(--primary-color);
            color: white;
        }
        .unread-badge {
            background: var(--danger-color);
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            float: right;
        }
        .messages-area {
            background: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .messages-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        .messages-body {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
        }
        .message {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }
        .message.sent {
            align-items: flex-end;
        }
        .message.received {
            align-items: flex-start;
        }
        .message-content {
            max-width: 70%;
            padding: 1rem;
            border-radius: 10px;
            word-wrap: break-word;
        }
        .message.sent .message-content {
            background: var(--primary-color);
            color: white;
        }
        .message.received .message-content {
            background: var(--bg-color);
            border: 1px solid var(--border-color);
        }
        .message-time {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.3rem;
        }
        .messages-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        .message-form {
            display: flex;
            gap: 1rem;
        }
        .message-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background: var(--bg-color);
            color: var(--text-color);
        }
        @media (max-width: 768px) {
            .messages-container {
                grid-template-columns: 1fr;
            }
            .conversations-list {
                height: auto;
                max-height: 300px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">🏘️ RealEstate</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="listings.php">Properties</a></li>
                <?php if (isSeller()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Messages</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="messages-container">
            <!-- Conversations List -->
            <div class="conversations-list">
                <h3 style="margin-bottom: 1rem;">Conversations</h3>
                <?php if ($conversations->num_rows > 0): ?>
                    <?php while ($conv = $conversations->fetch_assoc()): ?>
                        <div class="conversation-item <?php echo (isset($_GET['conversation_id']) && $_GET['conversation_id'] == $conv['other_user_id'] && $_GET['property_id'] == $conv['property_id']) ? 'active' : ''; ?>" 
                             onclick="window.location.href='messages.php?conversation_id=<?php echo $conv['other_user_id']; ?>&property_id=<?php echo $conv['property_id']; ?>'">
                            <div style="font-weight: bold;"><?php echo htmlspecialchars($conv['other_user_name']); ?></div>
                            <div style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo htmlspecialchars($conv['property_title']); ?></div>
                            <?php if ($conv['unread_count'] > 0): ?>
                                <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">No conversations yet</p>
                <?php endif; ?>
            </div>

            <!-- Messages Area -->
            <div class="messages-area">
                <?php if ($messages): ?>
                    <div class="messages-header">
                        <h3><?php echo htmlspecialchars($current_conversation['name']); ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">
                            Re: <?php echo htmlspecialchars($current_conversation['title']); ?>
                        </p>
                    </div>
                    
                    <div class="messages-body">
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message <?php echo ($msg['sender_id'] == $_SESSION['user_id']) ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($msg['message_text'])); ?>
                                </div>
                                <div class="message-time">
                                    <?php echo date('M d, Y g:i A', strtotime($msg['timestamp'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="messages-footer">
                        <form method="POST" action="" class="message-form">
                            <input type="hidden" name="receiver_id" value="<?php echo $conversation_id; ?>">
                            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                            <textarea name="message_text" class="message-input" rows="2" placeholder="Type your message..." required></textarea>
                            <button type="submit" name="send_message" class="btn btn-primary">Send</button>
                        </form>
                    </div>
                <?php elseif ($new_receiver_id && $new_property_id): ?>
                    <?php
                    // Get property and seller details
                    $new_stmt = $conn->prepare("SELECT p.title, u.name FROM properties p JOIN users u ON p.seller_id = u.id WHERE p.id = ? AND u.id = ?");
                    $new_stmt->bind_param("ii", $new_property_id, $new_receiver_id);
                    $new_stmt->execute();
                    $new_conv = $new_stmt->get_result()->fetch_assoc();
                    ?>
                    <div class="messages-header">
                        <h3><?php echo htmlspecialchars($new_conv['name']); ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.9rem;">
                            Re: <?php echo htmlspecialchars($new_conv['title']); ?>
                        </p>
                    </div>
                    
                    <div class="messages-body">
                        <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                            Start a conversation about this property
                        </p>
                    </div>

                    <div class="messages-footer">
                        <form method="POST" action="" class="message-form">
                            <input type="hidden" name="receiver_id" value="<?php echo $new_receiver_id; ?>">
                            <input type="hidden" name="property_id" value="<?php echo $new_property_id; ?>">
                            <textarea name="message_text" class="message-input" rows="2" placeholder="Type your message..." required></textarea>
                            <button type="submit" name="send_message" class="btn btn-primary">Send</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                        <p style="color: var(--text-secondary);">Select a conversation to view messages</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>