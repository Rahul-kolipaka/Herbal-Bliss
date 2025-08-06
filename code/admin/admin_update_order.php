<?php
include 'config.php';

// Redirect if not logged in as admin
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Get order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: admin_orders.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_status = $_POST['payment_status'];
    $order_status = $_POST['order_status'];
    
    try {
        $update_stmt = $conn->prepare("UPDATE orders SET payment_status = ?, order_status = ? WHERE id = ?");
        $update_stmt->execute([$payment_status, $order_status, $order_id]);
        
        $_SESSION['success'] = "Order updated successfully";
        header("Location: admin_order_details.php?id=$order_id");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Update failed: " . $e->getMessage();
        header("Location: admin_update_order.php?id=$order_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Order - Herbal Bliss</title>
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
        
        .update-form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c6e49;
        }
        
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿 - Update Order</h1>
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
        <h2>Update Order #<?= $order['id'] ?></h2>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="update-form">
            <form method="POST" action="admin_update_order.php?id=<?= $order['id'] ?>">
                <div class="form-group">
                    <label for="payment_status">Payment Status</label>
                    <select id="payment_status" name="payment_status" required>
                        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= $order['payment_status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="order_status">Order Status</label>
                    <select id="order_status" name="order_status" required>
                        <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                
                <button type="submit" class="auth-btn">Update Order</button>
                <a href="admin_order_details.php?id=<?= $order['id'] ?>" class="action-btn btn-danger" style="margin-left: 10px;">Cancel</a>
            </form>
        </div>
    </section>
</body>
</html>