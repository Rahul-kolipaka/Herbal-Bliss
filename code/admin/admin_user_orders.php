<?php
include 'config.php';

// Redirect if not logged in as admin
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$user_id = $_GET['user_id'] ?? 0;

// Get user details
$user_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: admin_users.php");
    exit();
}

// Get user's orders
$orders_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$orders_stmt->execute([$user_id]);
$orders = $orders_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Orders - Herbal Bliss</title>
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
        
        .user-info {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .user-info h3 {
            color: #2c6e49;
            margin-bottom: 10px;
        }
        
        .user-info p {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿 - User Orders</h1>
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
        <h2>Orders for <?= htmlspecialchars($user['name']) ?></h2>
        
        <div class="user-info">
            <h3>User Information</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Total Orders:</strong> <?= count($orders) ?></p>
        </div>
        
        <?php if(count($orders) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td>#<?= $order['id'] ?></td>
                        <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td>
                            <span class="status-badge status-<?= $order['payment_status'] ?>">
                                <?= ucfirst($order['payment_status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $order['order_status'] ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                        </td>
                        <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="admin_order_details.php?id=<?= $order['id'] ?>" class="action-btn btn-primary">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>This user hasn't placed any orders yet.</p>
        <?php endif; ?>
        
        <div style="margin-top: 20px;">
            <a href="admin_users.php" class="action-btn btn-primary">Back to Users</a>
        </div>
    </section>
</body>
</html>