<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Помощь - Оптовый Склад OptMoney</title>
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

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px; /* Ширина формы увеличена */
            margin: 0 auto;
        }

        .form-container h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group input[type="radio"] {
            width: auto;
            margin-top: 0;
        }

        .form-group .radio-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .form-group .radio-group label {
            margin-right: 10px;
        }

        .form-group button {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #218838;
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
    <div class="container">
        <div class="form-container">
            <h2>Свяжитесь с нами</h2>
            <form>
                <div class="form-group">
                    <label>Сообщение о:</label>
                    <div class="radio-group">
                        <label><input type="radio" name="message_about" value="Поиск товаров"> Поиск товаров</label>
                        <label><input type="radio" name="message_about" value="Консультация"> Консультация</label>
                        <label><input type="radio" name="message_about" value="Сотрудничество с Made-in-China.com"> Сотрудничество с Made-in-China.com</label>
                        <label><input type="radio" name="message_about" value="Жалобы и советы"> Жалобы и советы</label>
                        <label><input type="radio" name="message_about" value="Поделиться успешными историями"> Поделиться успешными историями</label>
                        <label><input type="radio" name="message_about" value="Другие"> Другие</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="subject">Тема:</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="comments">Комментарии:</label>
                    <textarea id="comments" name="comments" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="email">Электронная Почта:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="country_region">Страна / Регион:</label>
                    <input type="text" id="country_region" name="country_region" required>
                </div>
                <div class="form-group">
                    <label for="attachment">Приложение:</label>
                    <input type="file" id="attachment" name="attachment">
                </div>
                <div class="form-group">
                    <button type="submit">Отправить</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
