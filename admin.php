<?php
session_start();

// Подключение к базе данных
$database_host = "127.0.0.1";
$database_username = "root";
$database_password = "";
$database_name = "optmoney";

$conn = new mysqli($database_host, $database_username, $database_password, $database_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Проверка, была ли отправлена форма покупки
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['lot_id'])) {
        // Получаем user_id из сессии
        $user_id = $_SESSION['user_id'] ?? null;
        $lot_id = $_POST['lot_id'];

        if ($user_id && $lot_id) {
            $sql = "INSERT INTO purchases (user_id, lot_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $lot_id);

            if ($stmt->execute()) {
                echo "<script>alert('Покупка успешно добавлена!');</script>";
                // Логирование
                log_action("Пользователь с ID $user_id приобрел лот с ID $lot_id");
            } else {
                echo "<script>alert('Ошибка при добавлении покупки: " . $conn->error . "');</script>";
                // Логирование ошибки
                log_action("Ошибка при добавлении покупки: " . $conn->error);
            }

            $stmt->close();
        } else {
            echo "<script>alert('Ошибка: Невозможно получить ID пользователя или лота.');</script>";
            // Логирование ошибки
            log_action("Ошибка: Невозможно получить ID пользователя или лота.");
        }
    }
}

// Функция для логирования действий
function log_action($message) {
    error_log($message . "\n", 3, "auction_log.log");
}

// Получаем лоты из базы данных
$sql = "SELECT lots.*, images.image_data FROM lots LEFT JOIN images ON lots.image_id = images.id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Страница администратора</title>
    <link rel="stylesheet" type="text/css" href="/styles/main.css">
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

        .lots-description {
            margin: 20px auto;
            max-width: 800px;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .lots-description h2 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
        }

        .lots-description p {
            color: #555;
            margin-bottom: 20px;
        }

        .lots-description ul {
            list-style-type: none;
            padding-left: 0;
        }

        .lots-description li {
            margin-bottom: 10px;
        }

        .lots-description img {
            display: block;
            margin: 20px auto;
            max-width: 100%;
            height: auto;
        }

        .create-lot-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .create-lot-button:hover {
            background-color: #218838;
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
                    echo "<li><a href=\"/server/zakaz.php\">Заказы</a></li>"; // Новая кнопка "Заказы"
                    echo "<li><a href=\"/server/logout.php\">Выйти</a></li>";
                    echo "<li><a href=\"/server/create_lot.php\" class=\"create-lot-button\">Создание лота</a></li>";
                } else {
                    echo "<li><a href=\"/server/login.php\" class=\"btn-login\" >Войти</a></li>";
                    echo "<li><a href=\"/server/register.php\" class=\"btn-login\">Регистрация</a></li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="lots-description">
        <h1>Добро пожаловать, администратор!</h1>
        <h1>Здесь вы можете:</h1>
        <ul>
            <h2>Добавлять, изменять и удалять лоты.</h2>
            <h2>Управлять пользователями: добавлять, изменять и удалять их учетные записи.</h2>
            <h2>Использовать главное меню для навигации по административным функциям.</h2>
        </ul>
        <img src="https://resizer.mail.ru/p/2bb1d72f-32aa-575f-85a4-44ad29ca5b2c/AQAKf2SPXUh17VmtgcrMYL-prN7Hy5KXhipAo973spoZ-vzPjx5e6OP52VPVP83MmuK34GEZ13Dz2mVjSsGP9ZubkEE.jpg" alt="Description of the image">
    </div>
</body>
</html>
