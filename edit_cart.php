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

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$cart_id = $_POST['cart_id'];
$quantity = $_POST['quantity'];

$query = "UPDATE cart_items SET quantity = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $quantity, $cart_id);

if ($stmt->execute()) {
    header("Location: admin_cart.php");
} else {
    echo "Ошибка: " . $stmt->error;
}
?>
