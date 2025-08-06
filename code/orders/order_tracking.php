<?php
include 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to track your order";
    header("Location: auth/login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $conn->prepare("
    SELECT o.* 
    FROM orders o
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: my_orders.php");
    exit();
}

// Get order status timeline (simplified for this example)
$timeline = [
    ['status' => 'ordered', 'date' => $order['created_at'], 'completed' => true],
    ['status' => 'processing', 'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +1 hour')), 
     'completed' => in_array($order['order_status'], ['processing', 'shipped', 'delivered'])],
    ['status' => 'shipped', 'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +1 day')), 
     'completed' => in_array($order['order_status'], ['shipped', 'delivered'])],
    ['status' => 'delivered', 'date' => date('Y-m-d H:i:s', strtotime($order['created_at'] . ' +3 days')), 
     'completed' => $order['order_status'] === 'delivered']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tracking-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .timeline {
            position: relative;
            padding-left: 50px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #2c6e49;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -40px;
            top: 5px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: <?= $order['order_status'] === 'cancelled' ? '#dc3545' : '#2c6e49' ?>;
            border: 3px solid white;
            z-index: 1;
        }
        
        .timeline-item.completed::before {
            background-color: #2c6e49;
        }
        
        .timeline-item.current::before {
            background-color: #ffc107;
        }
        
        .timeline-item.cancelled::before {
            background-color: #dc3545;
        }
        
        .timeline-content {
            background: white;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .timeline-date {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
        <div class="tracking-container">
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <h2>Order Tracking</h2>
                        <h3>Order #<?= $order['id'] ?></h3>
                    </div>
                    <div>
                        <span class="status-badge status-<?= $order['order_status'] ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="timeline">
                    <?php foreach($timeline as $item): ?>
                        <?php 
                        $is_current = !$item['completed'] && 
                                     ($order['order_status'] !== 'cancelled') && 
                                     ($item['status'] === $order['order_status'] || 
                                      ($order['order_status'] === 'processing' && $item['status'] === 'ordered'));
                        ?>
                        <div class="timeline-item 
                            <?= $item['completed'] ? 'completed' : '' ?>
                            <?= $is_current ? 'current' : '' ?>
                            <?= $order['order_status'] === 'cancelled' && !$item['completed'] ? 'cancelled' : '' ?>">
                            <div class="timeline-content">
                                <h4><?= ucfirst($item['status']) ?></h4>
                                <?php if($item['completed']): ?>
                                    <p>Your order has been <?= $item['status'] ?>.</p>
                                <?php elseif($is_current): ?>
                                    <p>Your order is currently being <?= $item['status'] ?>.</p>
                                <?php elseif($order['order_status'] === 'cancelled'): ?>
                                    <p>Order was cancelled before this step.</p>
                                <?php else: ?>
                                    <p>Your order will be <?= $item['status'] ?> soon.</p>
                                <?php endif; ?>
                                <div class="timeline-date">
                                    <?= date('M d, Y h:i A', strtotime($item['date'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="order_details.php?id=<?= $order['id'] ?>" class="auth-btn">View Order Details</a>
                <a href="my_orders.php" class="auth-btn">Back to Orders</a>
            </div>
        </div>
    </section>
</body>
</html>