<?php
include 'config.php';

// Verify order exists
$order_id = $_GET['order_id'] ?? 0;
$amount = $_GET['amount'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Invalid order";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI Payment - Herbal Bliss</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .upi-qr {
            width: 250px;
            height: 250px;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .upi-details {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .payment-btn {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 20px 0;
            background: #2c6e49;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }
        .instructions {
            text-align: left;
            margin: 20px 0;
            padding: 10px;
            background: #fff3cd;
            border-radius: 5px;
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
        <div class="payment-container">
            <h2>Complete Your Payment</h2>
            <p>Order #<?= $order_id ?> - Total: ₹<?= number_format($amount, 2) ?></p>
            
            <div class="upi-details">
                <h3>Pay via UPI</h3>
                <p>Scan the QR code below using any UPI app</p>
                
                <!-- Your UPI QR Code -->
                <div class="upi-qr">

    <!--Upload your QR Image URL -->
                    <img src="https://9XXXXXXXXX@ybl=<?= $amount ?>&cu=INR" 
                         alt="UPI QR Code">
                </div>
                
    <!-- Metion UPI Id Here-->
                <p>UPI ID: <strong>9XXXXXXXXX@ybl</strong></p>
                <p>Name: <strong>Herbal Bliss</strong></p>
            </div>
            
            <div class="instructions">
                <h4>Payment Instructions:</h4>
                <ol>
                    <li>Open any UPI app on your phone</li>
                    <li>Scan the QR code above</li>
                    <li>Verify the amount and recipient details</li>
                    <li>Complete the payment</li>
                </ol>
            </div>
            
            <form action="payment_process.php" method="post">
                <input type="hidden" name="order_id" value="<?= $order_id ?>">
                <input type="hidden" name="amount" value="<?= $amount ?>">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <button type="submit" class="payment-btn">I've Completed the Payment</button>
            </form>
        </div>
    </section>
</body>
</html>