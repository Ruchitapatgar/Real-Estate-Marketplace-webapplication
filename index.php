<?php
require_once 'includes/config.php';

// Fetch featured properties
$featured_query = "SELECT p.*, u.name as seller_name FROM properties p 
                   JOIN users u ON p.seller_id = u.id 
                   ORDER BY p.created_at DESC LIMIT 6";
$featured_result = $conn->query($featured_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate Marketplace - Find Your Dream Property</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-screen">
        <div class="loader"></div>
    </div>

    <!-- Navigation -->
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

    <!-- Hero Section -->
    <div class="container">
        <section class="hero">
            <h1>Find Your Perfect Property</h1>
            <p>Connect with thousands of properties and trusted sellers. Your dream home is just a click away.</p>
            <div class="btn-group">
                <?php if (!isLoggedIn()): ?>
                    <a href="signup.php" class="btn btn-primary">Get Started</a>
                    <a href="listings.php" class="btn btn-secondary">Browse Properties</a>
                <?php else: ?>
                    <a href="listings.php" class="btn btn-primary">Browse Properties</a>
                    <?php if (isSeller()): ?>
                        <a href="dashboard.php" class="btn btn-secondary">My Dashboard</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Search Section -->
        <section class="search-section">
            <h2 style="margin-bottom: 1.5rem;">Search Properties</h2>
            <form action="listings.php" method="GET" class="search-form">
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="Enter city or area">
                </div>
                <div class="form-group">
                    <label for="type">Property Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <option value="House">House</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Villa">Villa</option>
                        <option value="Land">Land</option>
                        <option value="Commercial">Commercial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="min_price">Min Price</label>
                    <input type="number" id="min_price" name="min_price" placeholder="₹ Min">
                </div>
                <div class="form-group">
                    <label for="max_price">Max Price</label>
                    <input type="number" id="max_price" name="max_price" placeholder="₹ Max">
                </div>
                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Search</button>
                </div>
            </form>
        </section>

        <!-- Featured Properties -->
        <section>
            <h2 style="margin-bottom: 1.5rem;">Featured Properties</h2>
            <?php if ($featured_result && $featured_result->num_rows > 0): ?>
                <div class="property-grid">
                    <?php while ($property = $featured_result->fetch_assoc()): ?>
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
                                <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary" style="width: 100%;">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    No properties available yet. Be the first to list a property!
                </p>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Your trusted platform for buying and selling properties. We connect buyers and sellers across the country.</p>
                <div class="social-links">
                    <a href="#">📘</a>
                    <a href="#">🐦</a>
                    <a href="#">📷</a>
                    <a href="#">💼</a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="listings.php">Browse Properties</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    <li><a href="help.php">Help Center</a></li>
                    <li><a href="report.php">Report an Issue</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <ul>
                    <li>📧 info@realestate.com</li>
                    <li>📞 +91 99757855542</li>
                    <li>📍 alvas, Moodabidri, Karnataka</li>
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