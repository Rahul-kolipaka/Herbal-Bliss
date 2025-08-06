<?php
include 'config.php';

// Redirect if not logged in as admin
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Get all users with search
$search = $_GET['search'] ?? '';

$query = "SELECT id, name, email, created_at FROM users";
$params = [];

if (!empty($search)) {
    $query .= " WHERE name LIKE ? OR email LIKE ?";
    $search_term = "%$search%";
    $params = [$search_term, $search_term];
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Herbal Bliss</title>
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
        
        .filter-bar input, .filter-bar button {
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
        <h1>Herbal Bliss 🌿 - User Management</h1>
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
        <h2>User Management</h2>
        
        <div class="filter-bar">
            <form method="get" action="admin_users.php">
                <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
                <a href="admin_users.php" class="action-btn btn-primary">Reset</a>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Orders</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php
                        $order_count_stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
                        $order_count_stmt->execute([$user['id']]);
                        $order_count = $order_count_stmt->fetch()['order_count'];
                        ?>
                        <?= $order_count ?>
                    </td>
                    <td>
                        <a href="admin_user_orders.php?user_id=<?= $user['id'] ?>" class="action-btn btn-primary">View Orders</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</body>
</html>