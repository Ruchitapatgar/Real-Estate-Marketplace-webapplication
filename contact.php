<?php
require_once 'includes/config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $message = sanitize($_POST['message']);
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        
        if ($stmt->execute()) {
            $success = 'Thank you for contacting us! We will get back to you soon.';
            $_POST = array();
        } else {
            $error = 'Failed to send message. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 2rem;
        }
        .contact-info {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .contact-icon {
            font-size: 2rem;
        }
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
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
                <li><a href="contact.php">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Contact Us</h1>
        <p style="color: var(--text-secondary); margin-bottom: 2rem;">
            Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.
        </p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="contact-container">
            <div>
                <div class="search-section">
                    <h2 style="margin-bottom: 1.5rem;">Send us a Message</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>

            <div>
                <div class="contact-info">
                    <h2 style="margin-bottom: 1.5rem;">Get in Touch</h2>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div>
                            <h4>Address</h4>
                            <p style="color: var(--text-secondary);">
                                123 Real Estate Street<br>
                                Business District<br>
                                City, State 12345
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div>
                            <h4>Phone</h4>
                            <p style="color: var(--text-secondary);">+1 (555) 123-4567</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div>
                            <h4>Email</h4>
                            <p style="color: var(--text-secondary);">info@realestate.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">⏰</div>
                        <div>
                            <h4>Business Hours</h4>
                            <p style="color: var(--text-secondary);">
                                Monday - Friday: 9:00 AM - 6:00 PM<br>
                                Saturday: 10:00 AM - 4:00 PM<br>
                                Sunday: Closed
                            </p>
                        </div>
                    </div>

                    <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Follow Us</h3>
                    <div class="social-links">
                        <a href="#">📘</a>
                        <a href="#">🐦</a>
                        <a href="#">📷</a>
                        <a href="#">💼</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Your trusted platform for buying and selling properties.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="listings.php">Browse Properties</a></li>
                    <li><a href="help.php">Help Center</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Real Estate Marketplace. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>