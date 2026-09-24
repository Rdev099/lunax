<?php
session_start();
header('Content-Type: application/json');

$id  = $_POST['id'] ?? null;
$qty = isset($_POST['qty']) ? (int) $_POST['qty'] : null;

if ($id === null || $qty === null || !isset($_SESSION['cart'][$id])) {
    http_response_code(400);
    echo json_encode(['error' => 'Item not found']);
    exit;
}

if ($qty <= 0) {
    unset($_SESSION['cart'][$id]);
} else {
    $_SESSION['cart'][$id]['qty'] = $qty;
}

echo json_encode([
    'ok' => true,
    'itemCount' => array_sum(array_column($_SESSION['cart'], 'qty')),
]);