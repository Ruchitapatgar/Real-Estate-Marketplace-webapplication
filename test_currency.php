<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Test</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .test-box { background: white; padding: 30px; border-radius: 10px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; }
        .price { font-size: 2rem; color: #2563eb; font-weight: bold; margin: 20px 0; }
        .success { color: #10b981; padding: 10px; background: #d1fae5; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="test-box">
        <h1>💰 Currency Symbol Test</h1>
        
        <p><strong>Current Currency Configuration:</strong></p>
        <div class="success">
            Symbol: <?php echo CURRENCY_SYMBOL; ?><br>
            Code: <?php echo CURRENCY_CODE; ?>
        </div>

        <h2>Price Formatting Examples:</h2>
        
        <div class="price">
            <?php echo formatPrice(50000); ?>
        </div>
        
        <div class="price">
            <?php echo formatPrice(1500000); ?>
        </div>
        
        <div class="price">
            <?php echo formatPrice(25000.50); ?>
        </div>

        <hr style="margin: 30px 0;">

        <h2>How to Change Currency:</h2>
        <p>Edit <code>includes/config.php</code> and change these lines:</p>
        <pre style="background: #f9fafb; padding: 15px; border-radius: 5px; overflow-x: auto;">
define('CURRENCY_SYMBOL', '₹');  // Change this
define('CURRENCY_CODE', 'INR');  // Change this

Examples:
- For US Dollar:    '$ ' and 'USD'
- For Euro:         '€' and 'EUR'
- For Pound:        '£' and 'GBP'
- For Yen:          '¥' and 'JPY'
- For Rupee:        '₹' and 'INR'
        </pre>

        <p style="margin-top: 20px; text-align: center;">
            <a href="index.php" style="display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;">Go to Homepage</a>
        </p>
    </div>
</body>
</html>