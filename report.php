<?php
require_once 'includes/config.php';

$success = '';
$error = '';

// Get property_id if passed
$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : null;
$property_title = '';

if ($property_id) {
    $stmt = $conn->prepare("SELECT title FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $property_title = $result->fetch_assoc()['title'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
    $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : null;
    $report_type = sanitize($_POST['report_type']);
    $description = sanitize($_POST['description']);
    
    if (empty($report_type) || empty($description)) {
        $error = 'Please fill in all required fields';
    } else {
        $stmt = $conn->prepare("INSERT INTO reports (user_id, property_id, report_type, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $property_id, $report_type, $description);
        
        if ($stmt->execute()) {
            $success = 'Thank you for your report. We will review it and take appropriate action.';
            $_POST = array();
            $property_id = null;
        } else {
            $error = 'Failed to submit report. Please try again.';
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
    <title>Report Issue - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .report-container {
            max-width: 700px;
            margin: 2rem auto;
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
                <li><a href="help.php">Help</a></li>
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
        <div class="report-container">
            <h1>Report an Issue</h1>
            <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                Help us maintain a safe and trustworthy platform by reporting any fake listings, inappropriate content, 
                or suspicious activities. All reports are reviewed by our team.
            </p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="search-section">
                <form method="POST" action="">
                    <?php if ($property_id): ?>
                        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                            Reporting property: <strong><?php echo htmlspecialchars($property_title); ?></strong>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="report_type">Report Type *</label>
                        <select id="report_type" name="report_type" required>
                            <option value="">Select a reason</option>
                            <option value="Fake Listing">Fake Listing</option>
                            <option value="Fraudulent Activity">Fraudulent Activity</option>
                            <option value="Inappropriate Content">Inappropriate Content</option>
                            <option value="Spam">Spam</option>
                            <option value="Misleading Information">Misleading Information</option>
                            <option value="Duplicate Listing">Duplicate Listing</option>
                            <option value="Property Not Available">Property Not Available</option>
                            <option value="Harassment">Harassment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="6" 
                                  placeholder="Please provide details about the issue..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <small style="color: var(--text-secondary);">
                            Please be as detailed as possible. Include any relevant information that can help us investigate.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-danger">Submit Report</button>
                    <a href="<?php echo $property_id ? 'property.php?id=' . $property_id : 'listings.php'; ?>" 
                       class="btn btn-secondary" style="margin-left: 1rem;">Cancel</a>
                </form>
            </div>

            <div style="background: var(--card-bg); padding: 1.5rem; border-radius: 10px; margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem;">What happens next?</h3>
                <ul style="color: var(--text-secondary); line-height: 2;">
                    <li>Our team will review your report within 24-48 hours</li>
                    <li>We may contact you if we need additional information</li>
                    <li>Appropriate action will be taken if the report is verified</li>
                    <li>False reports may result in account restrictions</li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <p style="color: var(--text-secondary);">
                    For urgent matters or additional support, please <a href="contact.php" style="color: var(--primary-color);">contact us directly</a>.
                </p>
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