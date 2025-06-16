<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем, был ли отправлен ID лота для удаления
    if (isset($_POST['lot_id'])) {
        // Получаем ID лота из запроса
        $lot_id = $_POST['lot_id'];

        // Подключение к базе данных
        $database_host = "127.0.0.1";
        $database_username = "root";
        $database_password = "";
        $database_name = "optmoney";

        $conn = new mysqli($database_host, $database_username, $database_password, $database_name);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Запрос на удаление лота из базы данных
        $sql = "DELETE FROM purchases WHERE lot_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error preparing query: " . $conn->error);
        }
        $stmt->bind_param("i", $lot_id);
        $stmt->execute();
        $stmt->close();

        // Закрытие соединения с базой данных
        $conn->close();

        // Перенаправление обратно на страницу index.php после удаления
        header("Location: /index.php");
        exit();
    }
}
?>
