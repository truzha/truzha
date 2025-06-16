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

// Проверка, была ли отправлена форма изменения товара
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['lot_id'], $_POST['name'], $_POST['description'], $_POST['start_price'])) {
        $lot_id = $_POST['lot_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $start_price = $_POST['start_price'];

        $sql = "UPDATE lots SET name=?, description=?, start_price=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $name, $description, $start_price, $lot_id);

        if ($stmt->execute()) {
            echo "<script>alert('Товар успешно изменен!');</script>";
        } else {
            echo "<script>alert('Ошибка при изменении товара: " . $conn->error . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Ошибка: Не все данные для изменения товара были предоставлены.');</script>";
    }

    // Обработка изменения изображения
    if (isset($_POST['lot_id']) && isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $lot_id = $_POST['lot_id'];
        $image = $_FILES["image"]["tmp_name"];
        $imgContent = file_get_contents($image);

        $sql = "INSERT INTO images(image_data) VALUES(?)";
        $statement = $conn->prepare($sql);
        $statement->bind_param('s', $imgContent);
        if ($statement->execute()) {
            $image_id = $conn->insert_id;
            $sql = "UPDATE lots SET image_id=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $image_id, $lot_id);
            if ($stmt->execute()) {
                echo "<script>alert('Изображение товара успешно изменено!');</script>";
            } else {
                echo "<script>alert('Ошибка при изменении изображения товара: " . $conn->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Ошибка загрузки изображения, пожалуйста, попробуйте еще раз.');</script>";
        }
        $statement->close();
    }

    // Обработка удаления товара
    if (isset($_POST['delete_lot_id'])) {
        $lot_id = $_POST['delete_lot_id'];

        // Удаление всех записей в orders, связанных с этим лотом
        $sql = "DELETE FROM orders WHERE lot_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $lot_id);
        $stmt->execute();
        $stmt->close();

        // Теперь удаление самого лота
        $sql = "DELETE FROM lots WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $lot_id);

        if ($stmt->execute()) {
            echo "<script>alert('Товар успешно удален!');</script>";
        } else {
            echo "<script>alert('Ошибка при удалении товара: " . $conn->error . "');</script>";
        }

        $stmt->close();
    }
}

// Получаем товары из базы данных
$sql = "SELECT lots.*, images.image_data FROM lots LEFT JOIN images ON lots.image_id = images.id WHERE lots.id NOT IN (SELECT lot_id FROM purchases WHERE user_id = " . $_SESSION['user_id'] . ")";
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

        .lots {
            margin: 20px auto;
            max-width: 1200px;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .lots h1 {
            font-size: 2em;
            color: #333;
            margin-bottom: 20px;
        }

        .lot {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .lot img {
            max-width: 100%;
            max-height: 200px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .lot form {
            width: 100%;
        }

        .lot form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .lot form input[type="text"],
        .lot form input[type="number"],
        .lot form textarea,
        .lot form input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .lot form button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .lot form button:hover {
            background-color: #218838;
        }

        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-left: 10px;
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
                if (isset($_SESSION['username'])) {
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

    <div class="lots">
        <h1>На этой странице вы можете изменить значение товара</h1>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='lot'>";
                if (!empty($row['image_data'])) {
                    $imgData = base64_encode($row['image_data']);
                    echo "<img src='data:image/jpeg;base64,{$imgData}' alt='Изображение товара'>";
                } else {
                    echo "<img src='/path/to/default_image.jpg' alt='Изображение товара'>";
                }
                echo "<form method='post' enctype='multipart/form-data' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
                echo "<input type='hidden' name='lot_id' value='" . $row['id'] . "'>";
                echo "<label for='name" . $row['id'] . "'>Название:</label>";
                echo "<input type='text' id='name" . $row['id'] . "' name='name' value='" . $row['name'] . "' required>";
                echo "<label for='description" . $row['id'] . "'>Описание:</label>";
                echo "<textarea id='description" . $row['id'] . "' name='description' required>" . $row['description'] . "</textarea>";
                echo "<label for='start_price" . $row['id'] . "'>Оптовая цена:</label>";
                echo "<input type='number' id='start_price" . $row['id'] . "' name='start_price' value='" . $row['start_price'] . "' required>";
                echo "<label for='image" . $row['id'] . "'>Изображение:</label>";
                echo "<input type='file' id='image" . $row['id'] . "' name='image' accept='image/*'>";
                echo "<button type='submit'>Сохранить</button>";
                echo "<button type='submit' name='delete_lot_id' value='" . $row['id'] . "' class='delete-button'>Удалить</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "Товары не найдены";
        }
        ?>
    </div>
</body>
</html>
