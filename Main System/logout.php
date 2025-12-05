<?php
session_start();
session_unset();
session_destroy();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logged Out</title>
    <meta http-equiv="refresh" content="3;url=login.php">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #d9d9d9;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .logout-container {
            background: white;
            padding: 2rem 3rem;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            text-align: center;
            color: #2c3e50;
        }

        .logout-container h2 {
            margin-bottom: 1rem;
        }

        .logout-container p {
            font-size: 1rem;
        }

        .redirect-note {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <h2>You've been logged out</h2>
        <p>Thank you for using the Inventory Management System.</p>
        <div class="redirect-note">
            Redirecting to login page in 3 seconds...
        </div>
    </div>
</body>
</html>
