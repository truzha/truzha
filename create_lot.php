<?php
session_start();

$error_message = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION["username"];
    $lot_name = $_POST['lot_name'];
    $start_price = $_POST['start_price'];
    $description = $_POST['description'];

    $database_host = "127.0.0.1";
    $database_username = "root";
    $database_password = "";
    $database_name = "optmoney";

    $conn = new mysqli($database_host, $database_username, $database_password, $database_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $check_sql = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $seller_id = $check_stmt->get_result()->fetch_object()->id;

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $image = $_FILES["image"]["tmp_name"];
        $imgContent = file_get_contents($image);

        $sql = "INSERT INTO images(image_data) VALUES(?)";
        $statement = $conn->prepare($sql);
        $statement->bind_param('s', $imgContent);
        $current_id = $statement->execute() or die("<b>Error:</b> Problem on Image Insert<br/>" . mysqli_connect_error());

        if ($current_id) {
            $image_id = $conn->insert_id;
            $sql = "INSERT INTO lots(start_price, name, description, seller_id, image_id) VALUES(?, ?, ?, ?, ?)";
            $statement = $conn->prepare($sql);
            $statement->bind_param('issii', $start_price, $lot_name, $description, $seller_id, $image_id);
            if ($statement->execute()) {
                $success_message = "Лот успешно создан!";
            } else {
                $error_message = "Ошибка при создании лота: " . $conn->error;
            }
        } else {
            $error_message = "Ошибка загрузки изображения, пожалуйста, попробуйте еще раз.";
        }
        $statement->close();
    } else {
        $error_message = "Пожалуйста, выберите файл изображения для загрузки.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Аукционы</title>
    <link rel="stylesheet" type="text/css" href="/styles/main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .header {
            background-color: #343a40;
            color: #fff;
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

        main {
            padding: 20px;
            text-align: center;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form input[type="text"],
        form input[type="number"],
        form textarea,
        form button,
        form input[type="file"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        form button {
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #218838;
        }

        .admin-message {
            text-align: center;
            margin-bottom: 20px;
        }

        .admin-message h1 {
            font-size: 1.5em;
            color: #333;
        }

        .admin-message p {
            color: #555;
        }

        .admin-message img {
            display: block;
            margin: 20px auto;
            max-width: 100%;
            height: auto;
        }

        .message {
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .message.success {
            color: #28a745;
        }

        .message.error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Аукционы</h1>
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

    <main>
        <div class="admin-message">
            <h1>Уважаемый Администратор, на этой странице, ты можешь создать новую карточку товара</h1>
        </div>
        <?php
        if (!empty($success_message)) {
            echo "<div class='message success'>$success_message</div>";
        }
        if (!empty($error_message)) {
            echo "<div class='message error'>$error_message</div>";
        }
        ?>
        <section>
            <form action="/server/create_lot.php" method="post" enctype="multipart/form-data"> 
                <h2>Создание лота</h2>
                <input type="text" name="lot_name" placeholder="Название лота" required>
                <input type="number" min="1" step="any" name="start_price" placeholder="Начальная цена" required>
                <textarea name="description" placeholder="Описание лота" rows="4" required></textarea>
                <input type="file" name="image" accept="image/*" required>
                <button type="submit">Отправить</button>
            </form>
            
        </section>
    </main>
</body>
</html>
