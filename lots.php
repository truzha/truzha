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
    <div class="lots">
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

        // Обработка нажатия кнопки удаления лота
        if (isset($_POST['delete_lot'])) {
            $lot_id = $_POST['delete_lot'];
            $delete_sql = "DELETE FROM lots WHERE id = ?";
            $stmt = $conn->prepare($delete_sql);
            $stmt->bind_param("i", $lot_id);
            if ($stmt->execute()) {
                // Лот успешно удален
            } else {
                // Ошибка при удалении лота
            }
            $stmt->close();
        }

        // Проверка авторизации пользователя
        if (isset($_SESSION['username'])) {
            $check_sql = "SELECT id FROM users WHERE username = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $_SESSION['username']);
            $check_stmt->execute();
            $user_id = $check_stmt->get_result()->fetch_object()->id;
            $check_stmt->close();

            $sql = "SELECT * FROM lots WHERE seller_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $image_id = $row["image_id"];
                    $image_sql = "SELECT image_data FROM images WHERE id = ?";
                    $image_stmt = $conn->prepare($image_sql);
                    $image_stmt->bind_param("i", $row["image_id"]);
                    $image_stmt->execute();
                    $image_data = $image_stmt->get_result()->fetch_object()->image_data;
                    $image_stmt->close();
                    ?>

                    <div class="lot">
                        <div class="image">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($image_data); ?>"/>
                        </div>
                        <div class="details">
                            <h3><?php echo $row["name"]; ?></h3>
                            <p>Описание: <?php echo $row["description"]; ?></p>
                            <p>Начальная цена: <?php echo $row["start_price"]; ?></p>
                            <!-- Кнопка удаления лота -->
                            <form method="post">
                                <input type="hidden" name="delete_lot" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="delete-button">Удалить</button>
                            </form>
                        </div>
                    </div>

                    <?php
                }
            } else {
                echo "Лотов не найдено";
            }
            $stmt->close();
        }

        // Закрытие соединения с базой данных
        $conn->close();
        ?>
    </div>
</body>
</html>
