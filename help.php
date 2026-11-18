<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .faq-section {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }
        .faq-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        .faq-item:last-child {
            border-bottom: none;
        }
        .faq-question {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        .faq-answer {
            color: var(--text-secondary);
            line-height: 1.8;
        }
        .help-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .help-category {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
            text-align: center;
            transition: transform 0.3s ease;
        }
        .help-category:hover {
            transform: translateY(-5px);
        }
        .category-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
        <h1>Help Center</h1>
        <p style="color: var(--text-secondary); margin-bottom: 3rem;">
            Find answers to common questions and learn how to make the most of our platform.
        </p>

        <div class="help-categories">
            <div class="help-category">
                <div class="category-icon">🏠</div>
                <h3>For Buyers</h3>
                <p style="color: var(--text-secondary);">Learn how to search and find your perfect property</p>
            </div>
            <div class="help-category">
                <div class="category-icon">💼</div>
                <h3>For Sellers</h3>
                <p style="color: var(--text-secondary);">Get started with listing your properties</p>
            </div>
            <div class="help-category">
                <div class="category-icon">💬</div>
                <h3>Messaging</h3>
                <p style="color: var(--text-secondary);">Connect with buyers and sellers securely</p>
            </div>
            <div class="help-category">
                <div class="category-icon">🔒</div>
                <h3>Account & Security</h3>
                <p style="color: var(--text-secondary);">Manage your account and stay safe</p>
            </div>
        </div>

        <div class="faq-section">
            <h2 style="margin-bottom: 2rem;">Frequently Asked Questions</h2>

            <div class="faq-item">
                <div class="faq-question">How do I create an account?</div>
                <div class="faq-answer">
                    Click on the "Sign Up" button in the navigation bar. You'll need to provide your name, email, and create a password. 
                    You'll also choose whether you're registering as a buyer or seller. Once registered, you'll be redirected to the appropriate dashboard.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">How do I list a property as a seller?</div>
                <div class="faq-answer">
                    After logging in as a seller, go to your Dashboard. You'll find an "Add New Property" form where you can enter details 
                    about your property including title, description, price, size, location, and upload an image. Once submitted, 
                    your property will be visible to all buyers on the platform.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">How can I contact a seller about a property?</div>
                <div class="faq-answer">
                    When viewing a property details page, you'll see a "Message Seller" button. Click this to start a conversation 
                    with the property owner. All messages are private and can be accessed from your Messages page.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Can I edit or delete my property listing?</div>
                <div class="faq-answer">
                    Yes! From your Seller Dashboard, you can view all your properties. Each listing has "Edit" and "Delete" buttons. 
                    Use the Edit button to update property details or change the image. The Delete button will permanently remove the listing.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">How do I search for properties?</div>
                <div class="faq-answer">
                    Use the search filters on the home page or listings page. You can filter properties by location, property type, 
                    and price range. All results are displayed in real-time as you apply filters.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Is my information secure?</div>
                <div class="faq-answer">
                    Yes, we take security seriously. All passwords are encrypted using industry-standard hashing algorithms. 
                    Your personal information is never shared with third parties without your consent.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">How do I report a fake or inappropriate listing?</div>
                <div class="faq-answer">
                    On any property details page, you'll find a "Report this listing" link. Click it and fill out the report form 
                    describing the issue. Our team reviews all reports and takes appropriate action.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Can I switch between buyer and seller accounts?</div>
                <div class="faq-answer">
                    Currently, each account is designated as either a buyer or seller at registration. If you need to change your 
                    account type, please contact our support team through the Contact page.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">What should I include in my property description?</div>
                <div class="faq-answer">
                    Include detailed information about the property such as number of bedrooms/bathrooms, amenities, nearby facilities, 
                    condition, age of the property, and any unique features. Clear, honest descriptions help attract serious buyers.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">How do I enable dark mode?</div>
                <div class="faq-answer">
                    Click the theme toggle button (🌓) in the navigation bar to switch between light and dark modes. 
                    Your preference is saved automatically.
                </div>
            </div>
        </div>

        <div class="search-section">
            <h2 style="margin-bottom: 1rem;">Still need help?</h2>
            <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                Can't find what you're looking for? Get in touch with our support team.
            </p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="contact.php" class="btn btn-primary">Contact Support</a>
                <a href="report.php" class="btn btn-secondary">Report an Issue</a>
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
                    <li><a href="contact.php">Contact Us</a></li>
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