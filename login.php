<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isSeller()) {
        redirect('dashboard.php');
    } else {
        redirect('listings.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'All fields are required';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'seller') {
                    redirect('dashboard.php');
                } else {
                    redirect('listings.php');
                }
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
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
    <title>Login - Real Estate Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-container {
            max-width: 450px;
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
                <li><a href="signup.php">Sign Up</a></li>
                <li><button class="theme-toggle" onclick="toggleTheme()">🌓</button></li>
            </ul>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card">
            <h2>Welcome Back</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>