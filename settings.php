<?php
session_start();
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

        .container h2 {
            margin-bottom: 20px;
        }

        .lots {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .lot {
            width: 30%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background-color: white;
        }

        .image {
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .details {
            padding: 10px;
        }

        .details h3 {
            margin-top: 0;
        }

        .details p {
            margin: 5px 0;
        }

        .buy-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        .buy-button:hover {
            background-color: #218838;
        }

        /* Chat box styles */
        .chat-box {
            display: none;
            position: fixed;
            bottom: 10px;
            right: 10px;
            width: 300px;
            max-width: 100%;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: white;
            z-index: 1000;
        }

        .chat-header {
            background-color: #343a40;
            color: white;
            padding: 10px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 1em;
        }

        .chat-header button {
            background: none;
            border: none;
            color: white;
            font-size: 1.2em;
            cursor: pointer;
        }

        .chat-content {
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .chat-message {
            margin-bottom: 10px;
        }

        .chat-message.user {
            text-align: right;
        }

        .chat-message p {
            display: inline-block;
            padding: 10px;
            border-radius: 10px;
            background-color: #f4f4f4;
            margin: 0;
        }

        .chat-message.user p {
            background-color: #007bff;
            color: white;
        }

        .chat-input {
            display: flex;
            border-top: 1px solid #ccc;
        }

        .chat-input input {
            flex: 1;
            padding: 10px;
            border: none;
            border-bottom-left-radius: 10px;
        }

        .chat-input button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-bottom-right-radius: 10px;
            cursor: pointer;
        }

        .chat-input button:hover {
            background-color: #0056b3;
        }

        .open-chat-button {
            position: fixed;
            bottom: 10px;
            right: 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            z-index: 1000;
        }

        .open-chat-button:hover {
            background-color: #0056b3;
        }

        .operator-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .operator-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .operator-info .status {
            color: green;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Оптовый Склад OptMoney</h1>
        <div class="buttons">
            <ul>
                <li><a href="/index.php" style="color: #FFA07A;">Главная</a></li>
                <li><a href="/server/onac.php">О нас</a></li>
                <li><a href="/server/korzina.php">Корзина</a></li>
                <?php
                if(isset($_SESSION['username'])) {
                    echo "<li><a href=\"/server/settings.php\">Ваш профиль</a></li>";
                    echo "<li><a href=\"/server/logout.php\">Выйти</a></li>";
                } else {
                    echo "<li><a href=\"/server/login.php\" class=\"btn-login\">Войти</a></li>";
                    echo "<li><a href=\"/server/register.php\" class=\"btn-login\">Регистрация</a></li>";
                }
                ?>
            </ul>
        </div>
    </div>
    <main>
        <section>
            <h2>Здесь вы можете увидеть свои покупки<?php if (isset($_SESSION['username'])) { echo ", " . $_SESSION['username']; } ?>!</h2>
          
            <div class="lots">
                <?php
                // Подключение к базе данных
                $database_host = "127.0.0.1";
                $database_username = "root";
                $database_password = "";
                $database_name = "optmoney";

                $conn = new mysqli($database_host, $database_username, $database_password, $database_name);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Получение id текущего пользователя
                $username = $_SESSION['username'];
                $user_query = $conn->prepare("SELECT id FROM users WHERE username = ?");
                if (!$user_query) {
                    die("Error preparing query: " . $conn->error);
                }
                $user_query->bind_param("s", $username);
                $user_query->execute();
                $user_result = $user_query->get_result();
                if ($user_result->num_rows == 1) {
                    $user_id = $user_result->fetch_assoc()['id'];
                } else {
                    die("Ошибка: пользователь не найден.");
                }

                // Получение заказов, сделанных пользователем
                $sql = "SELECT lots.*, orders.quantity, orders.price, images.image_data 
                        FROM orders 
                        JOIN lots ON orders.lot_id = lots.id 
                        JOIN images ON lots.image_id = images.id 
                        WHERE orders.user_id = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Error preparing query: " . $conn->error);
                }
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='lot'>";
                        $image_data = base64_encode($row['image_data']); // Кодирование изображения в base64
                        echo "<div class='image'><img src='data:image/jpeg;base64," . $image_data . "' alt='Лот'></div>";
                        echo "<div class='details'>";
                        echo "<h3>" . $row["name"] . "</h3>";
                        echo "<p>Количество: " . $row["quantity"] . "</p>";
                        echo "<p>Цена за единицу: " . $row["price"] . "</p>";
                        echo "<p>Общая стоимость: " . ($row["quantity"] * $row["price"]) . "</p>";
                        echo "<p>Описание: " . $row["description"] . "</p>";
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "Заказы не найдены";
                }
                $conn->close();
                ?>
            </div>
        </section>
    </main>

    <button class="open-chat-button" onclick="openChat()">Чат поддержки</button>

    <div class="chat-box" id="chatBox">
        <div class="chat-header">
            <h3>Чат поддержки</h3>
            <button onclick="closeChat()">&times;</button>
        </div>
        <div class="chat-content" id="chatContent">
            <div class="chat-message operator">
                <div class="operator-info">
                    <img src="https://sun9-9.userapi.com/impg/ahMTUpqKKhrsOQpNGEOzMeiVTkKwR-Nac6Hbww/WO9fKfHCFT0.jpg?size=1280x1280&quality=95&sign=abf9cd828ed004da0957a26441628d99&type=album" alt="Кирилл">
                    <div>
                        <p><strong>Кирилл</strong> <span class="status">(онлайн)</span></p>
                        <p>Здравствуйте, я оператор сайта OptMoney, в чем заключается ваш вопрос?</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="chatInput" placeholder="Введите сообщение...">
            <button onclick="sendMessage()">Отправить</button>
        </div>
    </div>

    <script>
        function openChat() {
            document.getElementById('chatBox').style.display = 'block';
        }

        function closeChat() {
            document.getElementById('chatBox').style.display = 'none';
        }

        function sendMessage() {
            var input = document.getElementById('chatInput');
            var message = input.value.trim();
            if (message !== '') {
                var chatContent = document.getElementById('chatContent');
                var userMessage = document.createElement('div');
                userMessage.className = 'chat-message user';
                userMessage.innerHTML = '<p>' + message + '</p>';
                chatContent.appendChild(userMessage);
                chatContent.scrollTop = chatContent.scrollHeight;
                input.value = '';

                // Здесь вы можете добавить логику для отправки сообщения оператору, например, через AJAX
            }
        }
    </script>
</body>
</html>
