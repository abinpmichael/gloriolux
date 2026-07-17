<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - Add Second Slide Button</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f0f12;
            color: #e0e0e6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #1a1a24;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            max-width: 500px;
            width: 100%;
            text-align: center;
            border: 1px solid #c5a05933;
        }
        h2 {
            color: #c5a059;
            margin-top: 0;
        }
        .status {
            padding: 1rem;
            border-radius: 6px;
            margin: 1.5rem 0;
            font-weight: 500;
        }
        .success {
            background: rgba(40, 167, 69, 0.15);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        .error {
            background: rgba(220, 53, 69, 0.15);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        .info {
            background: rgba(23, 162, 184, 0.15);
            color: #17a2b8;
            border: 1px solid rgba(23, 162, 184, 0.3);
        }
        .btn {
            display: inline-block;
            background: #c5a059;
            color: #1a1a24;
            padding: 0.8rem 2rem;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        .btn:hover {
            background: #b38b22;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Database Migration</h2>
        <p>Adding second button fields to the <strong>slides</strong> table.</p>
        
        <?php
        require_once 'includes/db.php';
        try {
            $check = $pdo->query("SHOW COLUMNS FROM slides LIKE 'button2_text'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE slides 
                    ADD COLUMN button2_text VARCHAR(100) NULL AFTER button_url, 
                    ADD COLUMN button2_url VARCHAR(255) NULL AFTER button2_text");
                echo '<div class="status success">Success: Columns button2_text and button2_url successfully added!</div>';
            } else {
                echo '<div class="status info">Info: Columns button2_text and button2_url already exist.</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="status error">Error running migration: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<p style="font-size:0.9rem; color:#aaa;">Please ensure Apache and MySQL are running in your XAMPP Control Panel.</p>';
        }
        ?>
        
        <a href="index.php" class="btn">Go to Homepage</a>
    </div>
</body>
</html>
