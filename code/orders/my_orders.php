<?php
include 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your orders";
    header("Location: login.php");
    exit();
}

// Get user's orders
$stmt = $conn->prepare("
    SELECT o.*, COUNT(i.id) as item_count 
    FROM orders o
    LEFT JOIN order_items i ON o.id = i.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .orders-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-items {
            margin-top: 15px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-total {
            font-weight: bold;
            font-size: 18px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #2c6e49;
            text-align: right;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
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
        <div class="orders-container">
            <h2>My Orders</h2>
            
            <?php if(empty($orders)): ?>
                <p>You haven't placed any orders yet.</p>
                <a href="index.php" class="auth-btn">Start Shopping</a>
            <?php else: ?>
                <?php foreach($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h3>Order #<?= $order['id'] ?></h3>
                                <small><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></small>
                            </div>
                            <div>
                                <span class="status-badge status-<?= $order['order_status'] ?>">
                                    <?= ucfirst($order['order_status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-items">
                            <p><strong>Items: <?= $order['item_count'] ?></strong></p>
                            <p><strong>Total: ₹<?= number_format($order['total_amount'], 2) ?></strong></p>
                        </div>
                        
                        <div style="text-align: right; margin-top: 15px;">
                            <a href="order_details.php?id=<?= $order['id'] ?>" class="auth-btn">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>