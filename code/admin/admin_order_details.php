<?php
include 'config.php';

// Redirect if not logged in as admin
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: admin_orders.php");
    exit();
}

// Get order items
$items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Include all styles from admin_dashboard.php */
        .dashboard-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center; }
        .stat-card h3 { color: #2c6e49; margin-bottom: 10px; }
        .stat-card .value { font-size: 24px; font-weight: bold; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #2c6e49; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-failed { background-color: #f8d7da; color: #721c24; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #e2e3e5; color: #383d41; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .action-btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; margin-right: 5px; font-size: 12px; }
        .btn-primary { background-color: #2c6e49; color: white; }
        .btn-danger { background-color: #dc3545; color: white; }
        .admin-nav { background-color: #1e4d32; padding: 10px; margin-bottom: 20px; }
        .admin-nav ul { list-style: none; display: flex; gap: 20px; }
        .admin-nav a { color: white; text-decoration: none; font-weight: bold; }
        .admin-nav a:hover { text-decoration: underline; }
        
        .order-details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .order-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .order-section h3 {
            color: #2c6e49;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .order-info p {
            margin-bottom: 10px;
        }
        
        .order-info strong {
            display: inline-block;
            width: 150px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿 - Order Details</h1>
    </header>
    
    <div class="admin-nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="admin_orders.php">Orders</a></li>
            <li><a href="admin_users.php">Users</a></li>
            <li><a href="admin_logout.php">Logout</a></li>
        </ul>
    </div>

    <section id="content">
        <h2>Order #<?= $order['id'] ?></h2>
        
        <div class="order-details-container">
            <div class="order-section">
                <h3>Order Information</h3>
                <div class="order-info">
                    <p><strong>Order Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
                    <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                    <p><strong>User Account:</strong> 
                        <?php if($order['user_id']): ?>
                            <?= htmlspecialchars($order['user_name']) ?> (<?= htmlspecialchars($order['user_email']) ?>)
                        <?php else: ?>
                            Guest Checkout
                        <?php endif; ?>
                    </p>
                    <p><strong>Total Amount:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>
                    <p><strong>Payment Status:</strong> 
                        <span class="status-badge status-<?= $order['payment_status'] ?>">
                            <?= ucfirst($order['payment_status']) ?>
                        </span>
                    </p>
                    <p><strong>Order Status:</strong> 
                        <span class="status-badge status-<?= $order['order_status'] ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="order-section">
                <h3>Order Items</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td>₹<?= number_format($item['product_price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>₹<?= number_format($item['product_price'] * $item['quantity'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                            <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="admin_orders.php" class="action-btn btn-primary">Back to Orders</a>
            <a href="admin_update_order.php?id=<?= $order['id'] ?>" class="action-btn btn-primary">Update Order</a>
        </div>
    </section>
</body>
</html>