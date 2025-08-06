<?php
include '/config/config.php';

// Redirect if not logged in as admin
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Get all orders with filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "SELECT o.*, u.name as user_name, u.email as user_email FROM orders o LEFT JOIN users u ON o.user_id = u.id";
$params = [];

// Apply filters
if ($filter === 'pending') {
    $query .= " WHERE o.payment_status = 'pending'";
} elseif ($filter === 'completed') {
    $query .= " WHERE o.payment_status = 'completed'";
} elseif ($filter === 'delivered') {
    $query .= " WHERE o.order_status = 'delivered'";
} elseif ($filter === 'processing') {
    $query .= " WHERE o.order_status = 'processing'";
}


// Around line 30, fix the query concatenation
if (!empty($search)) {
    $query .= (strpos($query, 'WHERE') === false ? " WHERE " : " AND ");
    $query .= "(o.customer_name LIKE ? OR o.email LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Herbal Bliss</title>
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
        
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .filter-bar select, .filter-bar input, .filter-bar button {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .filter-bar button {
            background-color: #2c6e49;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿 - Order Management</h1>
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
        <h2>Order Management</h2>
        
        <div class="filter-bar">
            <form method="get" action="admin_orders.php">
                <select name="filter">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Orders</option>
                    <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending Payments</option>
                    <option value="completed" <?= $filter === 'completed' ? 'selected' : '' ?>>Completed Payments</option>
                    <option value="processing" <?= $filter === 'processing' ? 'selected' : '' ?>>Processing Orders</option>
                    <option value="delivered" <?= $filter === 'delivered' ? 'selected' : '' ?>>Delivered Orders</option>
                </select>
                
                <input type="text" name="search" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Apply</button>
                <a href="admin_orders.php" class="action-btn btn-primary">Reset</a>
            </form>
        </div>
        
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
                <?php foreach($orders as $order): ?>
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
                        <a href="admin_update_order.php?id=<?= $order['id'] ?>" class="action-btn btn-primary">Update</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</body>
</html>