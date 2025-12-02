<?php
/**
 * Quick Fix: Create requests table if it doesn't exist
 * Run this file once to create the missing requests table
 */

require 'db_connect.php';

try {
    // Create requests table
    $sql = "CREATE TABLE IF NOT EXISTS requests (
        request_id INT AUTO_INCREMENT PRIMARY KEY,
        ProductID INT NOT NULL,
        quantity INT NOT NULL,
        requester VARCHAR(255) NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_approved TIMESTAMP NULL,
        FOREIGN KEY (ProductID) REFERENCES products(ProductID) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($sql);
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Setup</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success { color: #28a745; padding: 15px; background: #d4edda; border-radius: 5px; margin: 20px 0; }
            .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            .btn:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>✓ Database Setup Complete</h1>
            <div class='success'>
                <strong>Success!</strong> The 'requests' table has been created successfully.
            </div>
            <p>The requests table is now ready to use.</p>
            <a href='request_list.php' class='btn'>Go to Requests Page</a>
            <a href='dashboard.php' class='btn' style='margin-left: 10px;'>Go to Dashboard</a>
        </div>
    </body>
    </html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Setup Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .container { background: white; padding: 30px; border-radius: 8px; max-width: 600px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .error { color: #dc3545; padding: 15px; background: #f8d7da; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>✗ Database Setup Error</h1>
            <div class='error'>
                <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
            </div>
            <p>Please check your database connection and ensure the 'products' table exists first.</p>
        </div>
    </body>
    </html>";
}
?>

