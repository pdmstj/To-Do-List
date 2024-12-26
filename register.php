<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        header("Location: login.php");
        exit();
    } else {
        $error = "모든 필드를 입력하세요.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #d4eaff, #a8d8ea);
            font-family: Arial, sans-serif;
        }

        .register-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .register-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .register-container form {
            display: flex;
            flex-direction: column;
        }

        .register-container input[type="text"],
        .register-container input[type="password"] {
            margin: 10px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .register-container input:focus {
            outline: none;
            border-color: #a8d8ea;
            box-shadow: 0 0 5px rgba(168, 216, 234, 0.5);
        }

        .register-container button {
            margin-top: 20px;
            padding: 15px;
            border: none;
            border-radius: 5px;
            background-color: #a8d8ea;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .register-container button:hover {
            background-color: #90cbe4;
        }

        .error-message {
            color: #ff4d4d;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1>회원가입</h1>
        <form action="" method="POST">
            <input type="text" name="username" placeholder="아이디" required>
            <input type="password" name="password" placeholder="비밀번호" required>
            <button type="submit">회원가입</button>
        </form>
        <?php if (isset($error)) { ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php } ?>
    </div>
</body>
</html>
