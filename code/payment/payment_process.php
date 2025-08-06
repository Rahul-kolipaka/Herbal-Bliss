<?php
include 'config.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Security token invalid";
    header("Location: checkout.php");
    exit();
}

$order_id = $_POST['order_id'] ?? 0;
$amount = $_POST['amount'] ?? 0;

// Verify order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Invalid order";
    header("Location: index.php");
    exit();
}

// Mark payment as completed
try {
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("
        UPDATE orders 
        SET payment_status = 'completed', 
            order_status = 'processing',
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$order_id]);
    
    $conn->commit();
    
    // Redirect to confirmation
    header("Location: order_confirmation.php?id=$order_id");
    exit();

} catch(PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = "Payment processing failed: " . $e->getMessage();
    header("Location: payment.php?order_id=$order_id&amount=$amount");
    exit();
}