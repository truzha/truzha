<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_check = $_POST['password_check'];

    if ($password !== $password_check) {
        echo "<script> alert(\"Пароли не совпадают!\"); window.location.href = \"/server/register.php\"; </script>";
        exit();
    }

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
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "Пользователь с таким именем уже существует.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_sql = "INSERT INTO users (username, password, role_id) VALUES (?, ?, 2)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $username, $hashed_password);

        if ($insert_stmt->execute()) {
            echo "<script> alert(\"Успешная регистрация! Пожалуйста, авторизуйтесь.\"); window.location.href = \"/server/login.php\"; </script>";
        } else {
            echo "Ошибка при регистрации.";
        }
    }

    $check_stmt->close();
    $insert_stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Форма регистрации</title>
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
    <form action="/server/register.php" method="post">
        <input type="text" class="form-control" name="username" id="username" placeholder="Введите логин">
        <input type="password" class="form-control" name="password" id="password" placeholder="Введите пароль">
        <input type="password" class="form-control" name="password_check" id="password_check" placeholder="Повторите пароль">
        <button class="btn" type="submit">Зарегистрировать</button>
    </form>
    <div class="links">
        <a href="/server/login.php">У вас уже есть аккаунт?</a>
    </div>
</div>
</body>
</html>
