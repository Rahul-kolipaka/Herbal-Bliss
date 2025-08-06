<?php
include 'config.php';

// Redirect if not logged in as admin
if(!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['error'] = "Please login to access the dashboard";
    header("Location: admin_login.php");
    exit();
}

// Get total revenue
$revenue_stmt = $conn->query("SELECT SUM(total_amount) as total_revenue FROM orders WHERE payment_status = 'completed'");
$total_revenue = $revenue_stmt->fetch()['total_revenue'] ?? 0;

// Get order counts
$order_counts_stmt = $conn->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN payment_status = 'completed' THEN 1 ELSE 0 END) as completed_payments,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
    FROM orders
");
$order_counts = $order_counts_stmt->fetch();

// Get recent orders
$orders_stmt = $conn->query("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recent_orders = $orders_stmt->fetchAll();

// Get all users
$users_stmt = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC");
$users = $users_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #2c6e49;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #2c6e49;
            color: white;
        }
        
        tr:hover {
            background-color: #f5f5f5;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
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
        
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            font-size: 12px;
        }
        
        .btn-primary {
            background-color: #2c6e49;
            color: white;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .admin-nav {
            background-color: #1e4d32;
            padding: 10px;
            margin-bottom: 20px;
        }
        
        .admin-nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        
        .admin-nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿 - Admin Dashboard</h1>
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
        <h2>Dashboard Overview</h2>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="value">₹<?= number_format($total_revenue, 2) ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value"><?= $order_counts['total_orders'] ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Completed Payments</h3>
                <div class="value"><?= $order_counts['completed_payments'] ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Delivered Orders</h3>
                <div class="value"><?= $order_counts['delivered_orders'] ?></div>
            </div>
        </div>
        
        <h2>Recent Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($order['customer_name']) ?><br>
                        <small><?= htmlspecialchars($order['email']) ?></small>
                    </td>
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
        
        <h2>Recent Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(array_slice($users, 0, 5) as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <a href="admin_user_orders.php?user_id=<?= $user['id'] ?>" class="action-btn btn-primary">Orders</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</body>
</html>