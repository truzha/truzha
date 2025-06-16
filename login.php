<?php
session_start();

$error_message = ""; // Инициализация переменной для сообщений об ошибке

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $database_host = "127.0.0.1";
    $database_username = "root";
    $database_password = "";
    $database_name = "optmoney";

    $conn = new mysqli($database_host, $database_username, $database_password, $database_name);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, username, password, role_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];  // Добавляем user_id в сессию
            $_SESSION["username"] = $user["username"];
            if ($user["role_id"] == 1){
                header("Location: /server/admin.php");
                exit();
            }
            header("Location: /index.php");
            exit();
        } else {
            $error_message = "Неправильный логин или пароль";
        }
    } else {
        $error_message = "Пользователь не найден";
    }

    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Форма Авторизации</title>
    <link rel="stylesheet" type="text/css" href="/styles/main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 300px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .form-control {
            width: calc(100% - 22px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .links {
            margin-top: 20px;
        }
        .links a {
            color: #FFA07A;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Оптовый Склад OptMoney</h1>
    </div>
    <form action="/server/login.php" method="post">
        <input type="text" class="form-control" name="username" id="username" placeholder="Введите логин">
        <input type="password" class="form-control" name="password" id="password" placeholder="Введите пароль">
        <button class="btn" type="submit">Войти</button>
        <?php if (!empty($error_message)) { ?> <p class="error"><?php echo $error_message; ?></p> <?php } ?>
    </form>
    <div class="links">
        <a href="/server/register.php">У вас еще нет аккаунта?</a>
    </div>
</div>
</body>
</html>
