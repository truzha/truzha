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

// Проверка, вошел ли пользователь в систему
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Обработка формы
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Запрос на общую информацию
$query = "
    SELECT 
        SUM(quantity) AS total_quantity,
        SUM(quantity * price) AS total_amount,
        COUNT(*) AS total_orders
    FROM orders
    WHERE delivery_date BETWEEN ? AND ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$total_quantity = $row['total_quantity'];
$total_amount = $row['total_amount'];
$total_orders = $row['total_orders'];

// Запрос на информацию по каждому товару
$item_query = "
    SELECT 
        lots.name,
        SUM(orders.quantity) AS total_quantity,
        SUM(orders.quantity * orders.price) AS total_amount
    FROM orders
    JOIN lots ON orders.lot_id = lots.id
    WHERE orders.delivery_date BETWEEN ? AND ?
    GROUP BY lots.name
    ORDER BY total_quantity DESC
";
$item_stmt = $conn->prepare($item_query);
$item_stmt->bind_param("ss", $start_date, $end_date);
$item_stmt->execute();
$item_result = $item_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчет о продажах</title>
    <link rel="stylesheet" href="main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2em;
        }

        .buttons {
            margin-top: 10px;
        }

        .buttons ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        .buttons li {
            margin: 0 15px;
        }

        .buttons a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .buttons a:hover {
            background-color: #495057;
        }

        .container {
            padding: 20px;
            text-align: center;
        }

        .report-form {
            margin-bottom: 20px;
        }

        .report-form input {
            padding: 10px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .report-form button {
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            background-color: #28a745;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .report-form button:hover {
            background-color: #218838;
        }

        .report-result {
            margin-top: 20px;
        }

        .report-result table {
            width: 50%;
            margin: 0 auto;
            border-collapse: collapse;
            border: 1px solid #ccc;
        }

        .report-result th, .report-result td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .report-result th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Отчет о продажах</h1>
        <div class="buttons">
            <ul>
                <li><a href="/server/admin.php" style="color: #FFA07A;">Главная</a></li>
                <?php
                if(isset($_SESSION['username'])) {
                    echo "<li><a href=\"/server/change_lot.php\">Изменить лот</a></li>";
                    echo "<li><a href=\"/server/delete_polz.php\">Удалить пользователя</a></li>";
                    echo "<li><a href=\"/server/zakaz.php\">Заказы</a></li>"; 
                    echo "<li><a href=\"/server/logout.php\">Выйти</a></li>";
                    echo "<li><a href=\"/server/create_lot.php\" class=\"create-lot-button\">Создание лота</a></li>";
                } else {
                    echo "<li><a href=\"/server/login.php\" class=\"btn-login\">Войти</a></li>";
                    echo "<li><a href=\"/server/register.php\" class=\"btn-login\">Регистрация</a></li>";
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="container">
        <h2>Выберите промежуток дат</h2>
        <form class="report-form" method="post">
            <label for="start_date">Начальная дата:</label>
            <input type="date" id="start_date" name="start_date" required>
            <label for="end_date">Конечная дата:</label>
            <input type="date" id="end_date" name="end_date" required>
            <button type="submit">Показать отчет</button>
        </form>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $total_orders > 0): ?>
        <div class="report-result">
            <h2>Отчет с <?php echo htmlspecialchars($start_date); ?> по <?php echo htmlspecialchars($end_date); ?></h2>
            <table>
                <tr>
                    <th>Общее количество проданных товаров</th>
                    <td><?php echo htmlspecialchars($total_quantity); ?></td>
                </tr>
                <tr>
                    <th>Общая сумма продаж</th>
                    <td><?php echo htmlspecialchars($total_amount); ?></td>
                </tr>
                <tr>
                    <th>Общее количество заказов</th>
                    <td><?php echo htmlspecialchars($total_orders); ?></td>
                </tr>
            </table>
            <h2>Информация по товарам</h2>
            <table>
                <tr>
                    <th>Название товара</th>
                    <th>Количество проданных единиц</th>
                    <th>Сумма продаж</th>
                </tr>
                <?php while ($item_row = $item_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item_row['name']); ?></td>
                    <td><?php echo htmlspecialchars($item_row['total_quantity']); ?></td>
                    <td><?php echo htmlspecialchars($item_row['total_amount']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="report-result">
            <h2>Нет данных для отображения за выбранный период.</h2>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
