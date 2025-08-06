<?php
include 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view this page";
    header("Location: login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, COUNT(i.id) as item_count 
    FROM orders o
    LEFT JOIN order_items i ON o.id = i.order_id
    WHERE o.id = ? AND o.user_id = ?
    GROUP BY o.id
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: my_orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .confirmation-icon {
            font-size: 60px;
            color: #2c6e49;
            margin-bottom: 20px;
        }
        
        .order-details {
            margin: 30px 0;
            text-align: left;
        }
        
        .order-details p {
            margin-bottom: 10px;
        }
        
        .order-details strong {
            display: inline-block;
            width: 150px;
        }
        
        .action-buttons {
            margin-top: 30px;
        }
        
        .action-buttons a {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="my_orders.php">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section id="content">
        <div class="confirmation-container">
            <div class="confirmation-icon">✓</div>
            <h2>Thank You for Your Order!</h2>
            <p>Your order has been received and is being processed.</p>
            
            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> #<?= $order['id'] ?></p>
                <p><strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                <p><strong>Total:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>
                <p><strong>Items:</strong> <?= $order['item_count'] ?></p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-<?= $order['order_status'] ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </p>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="auth-btn">Continue Shopping</a>
                <a href="my_orders.php" class="auth-btn">View All Orders</a>
            </div>
        </div>
    </section>
</body>
</html>