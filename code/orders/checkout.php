<?php
include 'config.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to checkout";
    header("Location: login.php");
    exit();
}

// Initialize cart
$cart = isset($_SESSION['cart']) ? json_decode($_SESSION['cart'], true) : [];
if (!is_array($cart)) $cart = [];

// Calculate total
$total = 0;
foreach ($cart as &$item) {
    $item['quantity'] = $item['quantity'] ?? 1;
    $total += $item['price'] * $item['quantity'];
}

if (empty($cart)) {
    $_SESSION['error'] = "Your cart is empty";
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Security token invalid";
        header("Location: checkout.php");
        exit();
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($email) || empty($address) || empty($phone)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: checkout.php");
        exit();
    }

    try {
        $conn->beginTransaction();

        // Insert order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, customer_name, email, address, phone, total_amount)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $name,
            $email,
            $address,
            $phone,
            $total
        ]);
        $order_id = $conn->lastInsertId();

        // Insert order items
        $item_stmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_name, product_price, quantity)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($cart as $item) {
            $item_stmt->execute([
                $order_id,
                $item['name'],
                $item['price'],
                $item['quantity']
            ]);
        }

        $conn->commit();
        unset($_SESSION['cart']);
        header("Location: payment.php?order_id=$order_id&amount=$total");
        exit();

    } catch(PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Checkout failed: " . $e->getMessage();
        error_log("Order Error: " . $e->getMessage());
        header("Location: checkout.php");
        exit();
    }
}
?>

<!-- Rest of your HTML remains the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .checkout-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .checkout-section h3 {
            color: #2c6e49;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c6e49;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .cart-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .cart-summary-total {
            font-weight: bold;
            font-size: 18px;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #2c6e49;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Herbal Bliss 🌿</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section id="content">
        <h2>Checkout</h2>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <div class="checkout-section">
                <h3>Shipping Information</h3>
                <form method="POST" action="checkout.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required 
                               value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Shipping Address</label>
                        <textarea id="address" name="address" rows="4" required></textarea>
                    </div>
                    
                    <button type="submit" class="auth-btn">Place Order</button>
                </form>
            </div>
            
            <div class="checkout-section">
                <h3>Order Summary</h3>
                <?php foreach($cart as $item): ?>
                    <div class="cart-summary-item">
                        <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <span>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-summary-total">
                    <span>Total:</span>
                    <span>₹<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>
    </section>
</body>
</html>