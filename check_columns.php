<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Column Check</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #2563eb; color: white; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .code-box { background: #f9fafb; padding: 15px; border-radius: 5px; margin: 20px 0; overflow-x: auto; }
        pre { margin: 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Column Check</h1>
        <p>This page checks if bedroom and bathroom columns exist in your database.</p>

        <h2>Properties Table Structure:</h2>
        <?php
        $result = $conn->query("DESCRIBE properties");
        if ($result):
        ?>
            <table>
                <tr>
                    <th>Column Name</th>
                    <th>Type</th>
                    <th>Null</th>
                    <th>Default</th>
                </tr>
                <?php
                $has_bedrooms = false;
                $has_bathrooms = false;
                $has_master_bathrooms = false;
                
                while ($row = $result->fetch_assoc()):
                    if ($row['Field'] == 'bedrooms') $has_bedrooms = true;
                    if ($row['Field'] == 'bathrooms') $has_bathrooms = true;
                    if ($row['Field'] == 'master_bathrooms') $has_master_bathrooms = true;
                ?>
                <tr>
                    <td><strong><?php echo $row['Field']; ?></strong></td>
                    <td><?php echo $row['Type']; ?></td>
                    <td><?php echo $row['Null']; ?></td>
                    <td><?php echo $row['Default'] ?? 'NULL'; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>

            <h2>Column Status:</h2>
            <table>
                <tr>
                    <th>Column</th>
                    <th>Status</th>
                </tr>
                <tr>
                    <td>bedrooms</td>
                    <td class="<?php echo $has_bedrooms ? 'success' : 'error'; ?>">
                        <?php echo $has_bedrooms ? '✅ EXISTS' : '❌ MISSING'; ?>
                    </td>
                </tr>
                <tr>
                    <td>bathrooms</td>
                    <td class="<?php echo $has_bathrooms ? 'success' : 'error'; ?>">
                        <?php echo $has_bathrooms ? '✅ EXISTS' : '❌ MISSING'; ?>
                    </td>
                </tr>
                <tr>
                    <td>master_bathrooms</td>
                    <td class="<?php echo $has_master_bathrooms ? 'success' : 'error'; ?>">
                        <?php echo $has_master_bathrooms ? '✅ EXISTS' : '❌ MISSING'; ?>
                    </td>
                </tr>
            </table>

            <?php if (!$has_bedrooms || !$has_bathrooms || !$has_master_bathrooms): ?>
                <div style="background: #fee2e2; padding: 20px; border-radius: 5px; border-left: 4px solid #ef4444; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #ef4444;">⚠️ Columns Missing!</h3>
                    <p>You need to add the missing columns to your database.</p>
                    <p><strong>Follow these steps:</strong></p>
                    <ol>
                        <li>Open phpMyAdmin: <a href="http://localhost/phpmyadmin/" target="_blank">http://localhost/phpmyadmin/</a></li>
                        <li>Select database: <strong>real_estate_db</strong></li>
                        <li>Click the <strong>SQL</strong> tab</li>
                        <li>Copy and paste this SQL code:</li>
                    </ol>
                    
                    <div class="code-box">
                        <pre>USE real_estate_db;

ALTER TABLE properties 
ADD COLUMN bedrooms INT DEFAULT 0 AFTER size,
ADD COLUMN bathrooms INT DEFAULT 0 AFTER bedrooms,
ADD COLUMN master_bathrooms INT DEFAULT 0 AFTER bathrooms;</pre>
                    </div>
                    
                    <p>5. Click <strong>Go</strong> button</p>
                    <p>6. Refresh this page to verify</p>
                </div>
            <?php else: ?>
                <div style="background: #d1fae5; padding: 20px; border-radius: 5px; border-left: 4px solid #10b981; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #10b981;">✅ All Columns Exist!</h3>
                    <p>Your database is configured correctly. The bedroom and bathroom fields should now be visible in your forms.</p>
                    
                    <h4>Next Steps:</h4>
                    <ol>
                        <li>Clear your browser cache (Ctrl + Shift + R)</li>
                        <li>Go to your <a href="dashboard.php">Dashboard</a></li>
                        <li>You should now see bedroom and bathroom fields in the "Add Property" form</li>
                    </ol>

                    <h4>If fields still don't show:</h4>
                    <ul>
                        <li>Make sure you replaced the <code>dashboard.php</code> file with the updated version</li>
                        <li>Check for any PHP errors at the top of the page</li>
                        <li>Try logging out and logging back in</li>
                    </ul>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <p class="error">❌ Could not retrieve table structure</p>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
            <a href="index.php" class="btn" style="background: #6b7280; margin-left: 10px;">Go to Homepage</a>
        </div>
    </div>
</body>
</html>