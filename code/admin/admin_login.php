<?php
include 'config.php';

// Redirect if already logged in as admin
if(isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token invalid. Please try again.";
        header("Location: admin_login.php");
        exit();
    }

    $login = trim($_POST['login']); // Can be username or email
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, username, email, password FROM admin_users WHERE username = ? OR email = ?");
        $stmt->execute([$login, $login]);
        
        if ($stmt->rowCount() === 1) {
            $admin = $stmt->fetch();
            
            if (password_verify($password, $admin['password'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_logged_in'] = true;
                
                // Generate new CSRF token
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                header("Location: admin_dashboard.php");
                exit();
            }
        }
        
        $_SESSION['error'] = "Invalid username/email or password";
        header("Location: admin_login.php");
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['error'] = "Login failed: " . $e->getMessage();
        header("Location: admin_login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿 - Admin</h1>
    </header>

    <section id="content">
        <div class="auth-form">
            <h2>Admin Login</h2>
            <?php if(isset($_SESSION['error'])): ?>
                <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <form action="admin_login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="form-group">
                    <label for="login">Username or Email</label>
                    <input type="text" id="login" name="login" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="auth-btn">Login</button>
            </form>
            <p>Don't have an account? <a href="admin_register.php">Register here</a></p>
        </div>
    </section>
</body>
</html>