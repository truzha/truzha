<?php
session_start();

// Перенаправление на login.php, если пользователь не авторизован
if (!isset($_SESSION['user_id'])) {
    header("Location: /server/login.php");
    exit();
}

// Подключение к базе данных
$database_host = "127.0.0.1";
$database_username = "root";
$database_password = "";
$database_name = "optmoney";

$conn = new mysqli($database_host, $database_username, $database_password, $database_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обновление количества товара в корзине
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id']) && isset($_POST['quantity'])) {
    $update_id = (int)$_POST['update_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity < 1) {
        $quantity = 1; // Чтобы не было отрицательных значений
    }
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("iii", $quantity, $update_id, $user_id);
        $stmt->execute();
    } else {
        echo "Ошибка подготовки запроса: " . $conn->error;
    }
}

// Удаление товара из корзины
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remove_id'])) {
    $remove_id = (int)$_POST['remove_id'];
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $remove_id, $user_id);
        $stmt->execute();
    } else {
        echo "Ошибка подготовки запроса: " . $conn->error;
    }
}

// Оформление заказа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $fio = $_POST['fio'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $delivery_date = $_POST['delivery_date'];
    $user_id = $_SESSION['user_id'];

    // Получаем все товары из корзины
    $stmt = $conn->prepare("SELECT lot_id, quantity, price FROM cart_items WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $lot_id = $row['lot_id'];
            $quantity = $row['quantity'];
            $price = $row['price'];
            $sql = "INSERT INTO orders (user_id, lot_id, fio, city, address, delivery_date, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql);
            if ($stmt_insert) {
                $stmt_insert->bind_param("iissssid", $user_id, $lot_id, $fio, $city, $address, $delivery_date, $quantity, $price);
                if ($stmt_insert->execute()) {
                    echo "<script>alert('Покупка успешно оформлена!');</script>";
                } else {
                    echo "<script>alert('Ошибка при оформлении покупки: " . $conn->error . "');</script>";
                }
                $stmt_insert->close();
            } else {
                echo "Ошибка подготовки запроса: " . $conn->error;
            }
        }

        // Очистка корзины после оформления заказа
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
        } else {
            echo "Ошибка подготовки запроса: " . $conn->error;
        }
    } else {
        echo "Ошибка подготовки запроса: " . $conn->error;
    }
}

// Получение товаров в корзине из базы данных
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT cart_items.id AS cart_id, lots.*, images.image_data, cart_items.quantity, cart_items.price FROM cart_items JOIN lots ON cart_items.lot_id = lots.id LEFT JOIN images ON lots.image_id = images.id WHERE cart_items.user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Ошибка подготовки запроса: " . $conn->error;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Корзина</title>
    <link rel="stylesheet" type="text/css" href="/styles/main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 2em;
        }

        .nav-bar {
            background-color: #343a40;
            color: white;
            padding: 10px 0;
            display: flex;
            justify-content: center;
        }

        .nav-bar a {
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            font-size: 1.2em;
        }

        .nav-bar a:hover {
            background-color: #495057;
            border-radius: 5px;
        }

        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            flex-wrap: wrap;
        }

        .cart-items {
            flex: 1;
            padding-right: 20px;
        }

        .cart-summary {
            width: 30%;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 20px;
        }

        .cart-item .details {
            flex: 1;
        }

        .cart-item .quantity {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 150px;
            margin-left: 10px;
        }

        .quantity button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .quantity button:hover {
            background-color: #0056b3;
        }

        .quantity input {
            width: 40px;
            padding: 5px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 0 10px;
            font-size: 1.2em;
        }

        .cart-item button.remove-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .cart-item button.remove-button:hover {
            background-color: #c82333;
        }

        .cart-summary h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .cart-summary .total {
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .buy-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 1.2em;
        }

        .buy-button:hover {
            background-color: #218838;
        }

        .cart-summary .info {
            margin-top: 30px;
            font-size: 1.1em;
        }

        .cart-summary .info p {
            margin: 5px 0;
        }

        /* Модальное окно */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        Оптовый Склад OptMoney
    </div>
    <div class="nav-bar">
        <a href="/index.php">Главная</a>
        <a href="/server/onac.php">О нас</a>
        <a href="/server/korzina.php">Корзина</a>
        <?php
        if (isset($_SESSION['username'])) {
            echo "<a href=\"/server/settings.php\">Ваш профиль</a>";
            echo "<a href=\"/server/logout.php\">Выйти</a>";
        } else {
            echo "<a href=\"/server/login.php\">Войти</a>";
            echo "<a href=\"/server/register.php\">Регистрация</a>";
        }
        ?>
    </div>

    <div class="container">
        <!-- Левый блок с товарами -->
        <div class="cart-items">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $total_price = $row['price'] * $row['quantity'];
                    echo "<div class='cart-item'>";
                    $image_data = base64_encode($row['image_data']);
                    echo "<img src='data:image/jpeg;base64," . $image_data . "' alt='Товар'>";
                    echo "<div class='details'>";
                    echo "<h3>" . $row["name"] . "</h3>";
                    echo "<p>Цена за единицу: " . $row["price"] . " Р</p>";
                    echo "<div class='quantity'>";
                    echo "<form method='post'>";
                    echo "<button type='submit' name='update_id' value='" . $row['cart_id'] . "'>-</button>";
                    echo "<input type='number' name='quantity' value='" . $row['quantity'] . "' min='1' readonly>";
                    echo "<button type='submit' name='update_id' value='" . $row['cart_id'] . "'>+</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "<p>Итого: " . $total_price . " Р</p>";
                    echo "</div>";
                    echo "<form method='post'>";
                    echo "<input type='hidden' name='remove_id' value='" . $row['cart_id'] . "'>";
                    echo "<button class='remove-button' type='submit'>Удалить из корзины</button>";
                    echo "</form>";
                    echo "</div>";
                }
            } else {
                echo "Ваша корзина пуста.";
            }
            ?>
        </div>

        <!-- Правый блок с оформлением заказа -->
        <div class="cart-summary">
            <h2>Ваша корзина</h2>
            <div class="total">
                <p>Товары: <?= $result->num_rows ?> шт.</p>
                <p>Сумма: <?= $total_price ?> Р</p>
            </div>
            <button class="buy-button" id="orderBtn">Оформить заказ</button>
            <div class="info">
                <p><strong>Товары в корзине:</strong></p>
                <?php
                    // Вывод краткой информации о товарах в корзине
                    $stmt = $conn->prepare("SELECT lots.name, cart_items.quantity FROM cart_items JOIN lots ON cart_items.lot_id = lots.id WHERE cart_items.user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result_info = $stmt->get_result();
                    while ($item = $result_info->fetch_assoc()) {
                        echo "<p>" . $item['name'] . " x" . $item['quantity'] . "</p>";
                    }
                ?>
            </div>
        </div>
    </div>

    <!-- Модальное окно -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Оформление заказа</h2>
            <form method="post">
                <div class="form-group">
                    <label for="fio">ФИО:</label>
                    <input type="text" id="fio" name="fio" required>
                </div>
                <div class="form-group">
                    <label for="city">Город:</label>
                    <input type="text" id="city" name="city" required>
                </div>
                <div class="form-group">
                    <label for="address">Адрес доставки:</label>
                    <input type="text" id="address" name="address" required>
                </div>
                <div class="form-group">
                    <label for="delivery_date">Дата доставки:</label>
                    <input type="date" id="delivery_date" name="delivery_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
                <input type="hidden" name="order" value="1">
                <button type="submit" class="buy-button">Подтвердить заказ</button>
            </form>
        </div>
    </div>

    <script>
        // Получаем модальное окно
        var modal = document.getElementById("orderModal");

        // Получаем кнопку, которая открывает модальное окно
        var btn = document.getElementById("orderBtn");

        // Получаем элемент <span>, который закрывает модальное окно
        var span = document.getElementsByClassName("close")[0];

        // Открываем модальное окно при нажатии на кнопку
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // Закрываем модальное окно при нажатии на <span> (x)
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Закрываем модальное окно при нажатии вне модального окна
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
