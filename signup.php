<?php
require_once 'includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!in_array($role, ['buyer', 'seller'])) {
        $error = 'Invalid role selected';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $success = 'Account created successfully! Redirecting to login...';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
    <title>Sign Up - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 4rem auto;
            padding: 2rem;
        }
        .auth-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .auth-card h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .role-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .role-option {
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        .role-option input[type="radio"] {
            display: none;
        }
        .role-option input[type="radio"]:checked + label {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }
        .role-option label {
            cursor: pointer;
            display: block;
            padding: 0.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        .role-option:hover {
            border-color: var(--primary-color);
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
        }
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">🏘️ RealEstate</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card">
            <h2>Create Your Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                    <small style="color: var(--text-secondary);">At least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label>I am a:</label>
                    <div class="role-selector">
                        <div class="role-option">
                            <input type="radio" id="buyer" name="role" value="buyer" required 
                                   <?php echo (isset($_POST['role']) && $_POST['role'] == 'buyer') ? 'checked' : ''; ?>>
                            <label for="buyer">
                                <div style="font-size: 2rem;">🏠</div>
                                <div>Buyer</div>
                                <small>Looking for property</small>
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" id="seller" name="role" value="seller" 
                                   <?php echo (isset($_POST['role']) && $_POST['role'] == 'seller') ? 'checked' : ''; ?>>
                            <label for="seller">
                                <div style="font-size: 2rem;">💼</div>
                                <div>Seller</div>
                                <small>Listing properties</small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Account</button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Add visual feedback for role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>
</html>