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

// Обновление статуса заказа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);

    if ($stmt->execute()) {
        echo "<script>alert('Статус заказа обновлен успешно!');</script>";
    } else {
        echo "<script>alert('Ошибка при обновлении статуса заказа: " . $conn->error . "');</script>";
    }
}

// Удаление заказа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_order_id'])) {
    $order_id = (int)$_POST['delete_order_id'];

    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo "<script>alert('Заказ успешно удален!');</script>";
    } else {
        echo "<script>alert('Ошибка при удалении заказа: " . $conn->error . "');</script>";
    }
}

// Запрос к базе данных для получения всех заказов
$query = "
    SELECT 
        orders.id AS order_id,
        users.username AS user_name,
        lots.name AS lot_name,
        orders.fio,
        orders.city,
        orders.address,
        orders.delivery_date,
        orders.date_ordered,
        orders.quantity,
        orders.price,
        orders.status
    FROM orders
    JOIN users ON orders.user_id = users.id
    JOIN lots ON orders.lot_id = lots.id
    ORDER BY orders.date_ordered DESC
";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказы пользователей</title>
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

        .report-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .report-button:hover {
            background-color: #0056b3;
        }

        .container {
            padding: 20px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .form-inline {
            display: flex;
            align-items: center;
        }

        .form-inline select {
            margin-left: 10px;
            padding: 5px;
        }

        .form-inline button {
            margin-left: 10px;
            padding: 5px 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-inline button:hover {
            background-color: #218838;
        }

        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Страница администратора</h1>
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
        <a href="report.php" class="report-button">Отчет</a>
        <h2>Все заказы пользователей</h2>
        <table>
            <thead>
                <tr>
                    <th>ID заказа</th>
                    <th>Пользователь</th>
                    <th>Товар</th>
                    <th>ФИО</th>
                    <th>Город</th>
                    <th>Адрес</th>
                    <th>Дата доставки</th>
                    <th>Дата заказа</th>
                    <th>Количество</th>
                    <th>Цена за единицу</th>
                    <th>Общая стоимость</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['lot_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['fio']); ?></td>
                        <td><?php echo htmlspecialchars($row['city']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['delivery_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['date_ordered']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity'] * $row['price']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <form class="form-inline" method="post" style="margin-bottom: 5px;">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                                <select name="status">
                                    <option value="ожидает проверки" <?php if ($row['status'] == 'ожидает проверки') echo 'selected'; ?>>ожидает проверки</option>
                                    <option value="в сборке" <?php if ($row['status'] == 'в сборке') echo 'selected'; ?>>в сборке</option>
                                    <option value="в пути" <?php if ($row['status'] == 'в пути') echo 'selected'; ?>>в пути</option>
                                    <option value="доставлен" <?php if ($row['status'] == 'доставлен') echo 'selected'; ?>>доставлен</option>
                                    <option value="отказано" <?php if ($row['status'] == 'отказано') echo 'selected'; ?>>отказано</option>
                                </select>
                                <button type="submit">Обновить</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="delete_order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                                <button type="submit" class="delete-button">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
