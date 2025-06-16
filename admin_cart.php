<?php
session_start();
$database_host = "127.0.0.1";
$database_username = "root";
$database_password = "";
$database_name = "optmoney";

$conn = new mysqli($database_host, $database_username, $database_password, $database_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$lot_id = $_POST['lot_id'];
$quantity = $_POST['quantity'];

// Проверка, если товар уже в корзине, обновить количество
$query_check = "SELECT id, quantity FROM cart_items WHERE user_id = ? AND lot_id = ?";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param("ii", $user_id, $lot_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Товар уже в корзине, обновляем количество
    $row = $result_check->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;
    $cart_item_id = $row['id'];
    
    $query_update = "UPDATE cart_items SET quantity = ? WHERE id = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("ii", $new_quantity, $cart_item_id);
    $stmt_update->execute();
} else {
    // Товар не в корзине, добавляем новый товар
    $query_insert = "INSERT INTO cart_items (user_id, lot_id, quantity) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("iii", $user_id, $lot_id, $quantity);
    $stmt_insert->execute();
}

header("Location: /index.php?success=1");
?>
