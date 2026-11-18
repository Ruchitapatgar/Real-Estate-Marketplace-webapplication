<?php
require_once 'includes/config.php';

if (!isLoggedIn() || !isSeller()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$property_id = intval($_GET['id']);
$success = '';
$error = '';

// Fetch property details
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    redirect('dashboard.php');
}

$property = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $size = sanitize($_POST['size']);
    $location = sanitize($_POST['location']);
    $property_type = sanitize($_POST['property_type']);
    $bedrooms = intval($_POST['bedrooms']);
    $bathrooms = intval($_POST['bathrooms']);
    $master_bathrooms = intval($_POST['master_bathrooms']);
    
    $image_path = $property['image_path'];
    
    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
                $image_path = $upload_path;
            }
        }
    }
    
    $stmt = $conn->prepare("UPDATE properties SET title = ?, description = ?, price = ?, size = ?, location = ?, property_type = ?, bedrooms = ?, bathrooms = ?, master_bathrooms = ?, image_path = ? WHERE id = ? AND seller_id = ?");
    $stmt->bind_param("ssdsssiissii", $title, $description, $price, $size, $location, $property_type, $bedrooms, $bathrooms, $master_bathrooms, $image_path, $property_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        $success = 'Property updated successfully!';
        // Refresh property data
        $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND seller_id = ?");
        $stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
        $stmt->execute();
        $property = $stmt->get_result()->fetch_assoc();
    } else {
        $error = 'Failed to update property.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">🏘️ RealEstate</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <a href="dashboard.php" style="color: var(--primary-color); text-decoration: none; display: inline-block; margin-bottom: 1rem;">
            ← Back to Dashboard
        </a>

        <h1>Edit Property</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <section class="search-section">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="search-form">
                    <div class="form-group">
                        <label for="title">Property Title *</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="property_type">Property Type *</label>
                        <select id="property_type" name="property_type" required>
                            <option value="House" <?php echo $property['property_type'] == 'House' ? 'selected' : ''; ?>>House</option>
                            <option value="Apartment" <?php echo $property['property_type'] == 'Apartment' ? 'selected' : ''; ?>>Apartment</option>
                            <option value="Villa" <?php echo $property['property_type'] == 'Villa' ? 'selected' : ''; ?>>Villa</option>
                            <option value="Land" <?php echo $property['property_type'] == 'Land' ? 'selected' : ''; ?>>Land</option>
                            <option value="Commercial" <?php echo $property['property_type'] == 'Commercial' ? 'selected' : ''; ?>>Commercial</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₹) *</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo $property['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="size">Size (sq ft) *</label>
                        <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($property['size']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bedrooms">Bedrooms 🛏️ *</label>
                        <input type="number" id="bedrooms" name="bedrooms" min="0" value="<?php echo isset($property['bedrooms']) ? $property['bedrooms'] : 0; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bathrooms">Bathrooms 🚿 *</label>
                        <input type="number" id="bathrooms" name="bathrooms" min="0" value="<?php echo isset($property['bathrooms']) ? $property['bathrooms'] : 0; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="master_bathrooms">Master Bathrooms 🛁</label>
                        <input type="number" id="master_bathrooms" name="master_bathrooms" min="0" value="<?php echo isset($property['master_bathrooms']) ? $property['master_bathrooms'] : 0; ?>">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="location">Location *</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($property['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="image">Change Property Image</label>
                        <?php if ($property['image_path']): ?>
                            <img src="<?php echo htmlspecialchars($property['image_path']); ?>" 
                                 style="max-width: 200px; margin-bottom: 1rem; border-radius: 5px; display: block;">
                        <?php endif; ?>
                        <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this, 'preview')">
                        <img id="preview" style="max-width: 200px; margin-top: 1rem; display: none; border-radius: 5px;">
                    </div>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Update Property</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>