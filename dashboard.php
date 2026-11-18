<?php
require_once 'includes/config.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle property addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_property'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $size = sanitize($_POST['size']);
    $location = sanitize($_POST['location']);
    $property_type = sanitize($_POST['property_type']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $master_bathrooms = intval($_POST['master_bathrooms']);
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Check if uploads folder exists, create if not
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            } else {
                $error = 'Failed to upload image. Check folder permissions.';
            }
        } else {
            $error = 'Invalid file type. Only JPG, JPEG, PNG, and GIF allowed.';
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO properties (seller_id, title, description, price, size, location, property_type, bedrooms, bathrooms, master_bathrooms, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdsssiiss", $_SESSION['user_id'], $title, $description, $price, $size, $location, $property_type, $bedrooms, $bathrooms, $master_bathrooms, $image_path);
    
    if ($stmt->execute()) {
        $success = 'Property added successfully!';
    } else {
        $error = 'Failed to add property. Please try again.';
    }
    $stmt->close();
}

// Handle property deletion
if (isset($_GET['delete'])) {
    $property_id = intval($_GET['delete']);
    
    // Get image path before deleting
    $stmt = $conn->prepare("SELECT image_path FROM properties WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();
        
        // Delete property
        $delete_stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND seller_id = ?");
        $delete_stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
        
        if ($delete_stmt->execute()) {
            // Delete image file
            if ($property['image_path'] && file_exists($property['image_path'])) {
                unlink($property['image_path']);
            }
            $success = 'Property deleted successfully!';
        }
        $delete_stmt->close();
    }
    $stmt->close();
}

// Fetch seller's properties
$properties_query = "SELECT * FROM properties WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($properties_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$properties_result = $stmt->get_result();

// Fetch unread messages count
$messages_query = "SELECT COUNT(*) as unread FROM messages WHERE receiver_id = ? AND is_read = 0";
$msg_stmt = $conn->prepare($messages_query);
$msg_stmt->bind_param("i", $_SESSION['user_id']);
$msg_stmt->execute();
$unread_count = $msg_stmt->get_result()->fetch_assoc()['unread'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">🏘️ RealEstate</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="listings.php">Properties</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="messages.php">Messages <?php if($unread_count > 0) echo "($unread_count)"; ?></a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Seller Dashboard</h1>
        <p style="color: var(--text-secondary); margin-bottom: 2rem;">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Property Section -->
        <section class="search-section">
            <h2 style="margin-bottom: 1.5rem;">Add New Property</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="search-form">
                    <div class="form-group">
                        <label for="title">Property Title *</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="property_type">Property Type *</label>
                        <select id="property_type" name="property_type" required>
                            <option value="House">House</option>
                            <option value="Apartment">Apartment</option>
                            <option value="Villa">Villa</option>
                            <option value="Land">Land</option>
                            <option value="Commercial">Commercial</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₹) *</label>
                        <input type="number" id="price" name="price" step="0.01" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="size">Size (sq ft) *</label>
                        <input type="text" id="size" name="size" placeholder="e.g., 2000 sq ft" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bedrooms">Bedrooms 🛏️ *</label>
                        <input type="number" id="bedrooms" name="bedrooms" min="0" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bathrooms">Bathrooms 🚿 *</label>
                        <input type="number" id="bathrooms" name="bathrooms" min="0" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="master_bathrooms">Master Bathrooms 🛁</label>
                        <input type="number" id="master_bathrooms" name="master_bathrooms" min="0" value="0">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" placeholder="City, Area" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="image">Property Image</label>
                        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this, 'preview')">
                        <img id="preview" style="max-width: 200px; margin-top: 1rem; display: none; border-radius: 5px;">
                    </div>
                </div>
                <button type="submit" name="add_property" class="btn btn-primary">Add Property</button>
            </form>
        </section>

        <!-- My Properties -->
        <section>
            <h2 style="margin-bottom: 1.5rem;">My Properties</h2>
            <?php if ($properties_result->num_rows > 0): ?>
                <div class="property-grid">
                    <?php while ($property = $properties_result->fetch_assoc()): ?>
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
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary" style="flex: 1;">View</a>
                                    <a href="edit_property.php?id=<?php echo $property['id']; ?>" class="btn btn-success" style="flex: 1;">Edit</a>
                                    <a href="?delete=<?php echo $property['id']; ?>" 
                                       class="btn btn-danger" 
                                       style="flex: 1;"
                                       onclick="return confirmDelete('Are you sure you want to delete this property?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-secondary); padding: 2rem;">
                    You haven't added any properties yet. Add your first property above!
                </p>
            <?php endif; ?>
        </section>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>