<?php
session_start();

// Get the posted cart data
$cart = json_decode(file_get_contents('php://input'), true);

if ($cart) {
    $_SESSION['cart'] = json_encode($cart);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid cart data']);
}
?>