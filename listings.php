<?php
require_once 'includes/config.php';

// Build query based on filters
$where_clauses = [];
$params = [];
$types = '';

if (isset($_GET['location']) && !empty($_GET['location'])) {
    $where_clauses[] = "location LIKE ?";
    $params[] = '%' . $_GET['location'] . '%';
    $types .= 's';
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_clauses[] = "property_type = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}

if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where_clauses[] = "price >= ?";
    $params[] = floatval($_GET['min_price']);
    $types .= 'd';
}

if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where_clauses[] = "price <= ?";
    $params[] = floatval($_GET['max_price']);
    $types .= 'd';
}

$query = "SELECT p.*, u.name as seller_name FROM properties p 
          JOIN users u ON p.seller_id = u.id";

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY p.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Listings - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">🏘️ RealEstate</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="listings.php">Properties</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="help.php">Help</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isSeller()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Browse Properties</h1>
        
        <!-- Search Filters -->
        <section class="search-section">
            <h2 style="margin-bottom: 1.5rem;">Filter Properties</h2>
            <form action="listings.php" method="GET" class="search-form">
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" 
                           value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>" 
                           placeholder="Enter city or area">
                </div>
                <div class="form-group">
                    <label for="type">Property Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <option value="House" <?php echo (isset($_GET['type']) && $_GET['type'] == 'House') ? 'selected' : ''; ?>>House</option>
                        <option value="Apartment" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Apartment') ? 'selected' : ''; ?>>Apartment</option>
                        <option value="Villa" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Villa') ? 'selected' : ''; ?>>Villa</option>
                        <option value="Land" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Land') ? 'selected' : ''; ?>>Land</option>
                        <option value="Commercial" <?php echo (isset($_GET['type']) && $_GET['type'] == 'Commercial') ? 'selected' : ''; ?>>Commercial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="min_price">Min Price</label>
                    <input type="number" id="min_price" name="min_price" 
                           value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>" 
                           placeholder="₹ Min">
                </div>
                <div class="form-group">
                    <label for="max_price">Max Price</label>
                    <input type="number" id="max_price" name="max_price" 
                           value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" 
                           placeholder="₹ Max">
                </div>
                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Search</button>
                </div>
                <div class="form-group" style="align-self: flex-end;">
                    <a href="listings.php" class="btn btn-secondary" style="width: 100%; text-align: center;">Clear Filters</a>
                </div>
            </form>
        </section>

        <!-- Property Listings -->
        <section>
            <h2 style="margin-bottom: 1.5rem;">
                Available Properties 
                <span style="color: var(--text-secondary); font-size: 1rem;">(<?php echo $result->num_rows; ?> found)</span>
            </h2>
            
            <?php if ($result->num_rows > 0): ?>
                <div class="property-grid">
                    <?php while ($property = $result->fetch_assoc()): ?>
                        <div class="property-card">
                            <img src="<?php echo htmlspecialchars($property['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>" 
                                 class="property-image">
                            <div class="property-content">
                                <h3 class="property-title"><?php echo htmlspecialchars($property['title']); ?></h3>
                                <p class="property-location">📍 <?php echo htmlspecialchars($property['location']); ?></p>
                                <div class="property-details">
                                    <span>📏 <?php echo htmlspecialchars($property['size']); ?></span>
                                    <span>🏷️ <?php echo htmlspecialchars($property['property_type']); ?></span>
                                </div>
                                <div class="property-details">
                                    <span>🛏️ <?php echo $property['bedrooms']; ?> Beds</span>
                                    <span>🚿 <?php echo $property['bathrooms']; ?> Baths</span>
                                </div>
                                <p class="property-price"><?php echo formatPrice($property['price']); ?></p>
                                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">
                                    Listed by: <?php echo htmlspecialchars($property['seller_name']); ?>
                                </p>
                                <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary" style="width: 100%;">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    No properties found matching your criteria. Try adjusting your filters.
                </p>
            <?php endif; ?>
        </section>
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
                    <li><a href="report.php">Report an Issue</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <ul>
                    <li>📧 info@realestate.com</li>
                    <li>📞 +1 (555) 123-4567</li>
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