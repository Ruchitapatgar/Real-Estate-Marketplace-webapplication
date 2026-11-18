<?php
require_once 'includes/config.php';

if (!isset($_GET['id'])) {
    redirect('listings.php');
}

$property_id = intval($_GET['id']);

// Fetch property details
$stmt = $conn->prepare("SELECT p.*, u.name as seller_name, u.email as seller_email, u.id as seller_id 
                        FROM properties p 
                        JOIN users u ON p.seller_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('listings.php');
}

$property = $result->fetch_assoc();
$is_owner = isLoggedIn() && $_SESSION['user_id'] == $property['seller_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .property-detail-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        .property-main-image {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 10px;
        }
        .property-info-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .seller-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 100px;
        }
        @media (max-width: 768px) {
            .property-detail-container {
                grid-template-columns: 1fr;
            }
            .property-main-image {
                height: 300px;
            }
            .seller-card {
                position: static;
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
                <?php if (isLoggedIn()): ?>
                    <?php if (isSeller()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <a href="listings.php" style="color: var(--primary-color); text-decoration: none; display: inline-block; margin-bottom: 1rem;">
            ← Back to Listings
        </a>

        <div class="property-detail-container">
            <div>
                <?php
                // Debug: Check what image path we have
                $image_to_display = $property['image_path'];
                
                // If no image or file doesn't exist, use placeholder
                if (empty($image_to_display) || !file_exists($image_to_display)) {
                    $image_to_display = 'assets/images/placeholder.jpg';
                }
                ?>
                <img src="<?php echo htmlspecialchars($image_to_display); ?>" 
                     alt="<?php echo htmlspecialchars($property['title']); ?>" 
                     class="property-main-image"
                     onerror="this.src='assets/images/placeholder.jpg'">
                
                <div class="property-info-card" style="margin-top: 2rem;">
                    <h1 style="margin-bottom: 1rem;"><?php echo htmlspecialchars($property['title']); ?></h1>
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                        📍 <?php echo htmlspecialchars($property['location']); ?>
                    </p>
                    <p style="font-size: 2rem; color: var(--primary-color); font-weight: bold; margin-bottom: 2rem;">
                        <?php echo formatPrice($property['price']); ?>
                    </p>
                    
                    <h3 style="margin-bottom: 1rem;">Property Details</h3>
                    <div class="info-row">
                        <span>Type:</span>
                        <strong><?php echo htmlspecialchars($property['property_type']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Size:</span>
                        <strong><?php echo htmlspecialchars($property['size']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Bedrooms:</span>
                        <strong>🛏️ <?php echo $property['bedrooms']; ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Bathrooms:</span>
                        <strong>🚿 <?php echo $property['bathrooms']; ?></strong>
                    </div>
                    <?php if ($property['master_bathrooms'] > 0): ?>
                    <div class="info-row">
                        <span>Master Bathrooms:</span>
                        <strong>🛁 <?php echo $property['master_bathrooms']; ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span>Listed:</span>
                        <strong><?php echo date('M d, Y', strtotime($property['created_at'])); ?></strong>
                    </div>
                    
                    <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Description</h3>
                    <p style="line-height: 1.8;"><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
            </div>

            <div>
                <div class="seller-card">
                    <h3 style="margin-bottom: 1rem;">Contact Seller</h3>
                    <div style="margin-bottom: 1.5rem;">
                        <p style="font-weight: bold; font-size: 1.1rem;"><?php echo htmlspecialchars($property['seller_name']); ?></p>
                        <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($property['seller_email']); ?></p>
                    </div>
                    
                    <?php if (!$is_owner): ?>
                        <?php if (isLoggedIn()): ?>
                            <a href="messages.php?property_id=<?php echo $property_id; ?>&seller_id=<?php echo $property['seller_id']; ?>" 
                               class="btn btn-primary" style="width: 100%; text-align: center;">
                                💬 Message Seller
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary" style="width: 100%; text-align: center;">
                                Login to Message Seller
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-success">This is your property</div>
                        <a href="edit_property.php?id=<?php echo $property_id; ?>" class="btn btn-success" style="width: 100%; text-align: center; margin-top: 1rem;">
                            Edit Property
                        </a>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="report.php?property_id=<?php echo $property_id; ?>" 
                           style="display: block; text-align: center; margin-top: 1rem; color: var(--danger-color); text-decoration: none;">
                            🚩 Report this listing
                        </a>
                    <?php endif; ?>
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
                    <li><a href="contact.php">Contact Us</a></li>
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