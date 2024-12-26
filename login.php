<?php
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) { // 로그인 처리
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: todo.php");
            exit();
        } else {
            $error = "로그인 실패. 아이디와 비밀번호를 확인하세요.";
        }
    } elseif (isset($_POST['register'])) { // 회원가입 처리
        $username = trim($_POST['username']);
        $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

        if (!empty($username) && !empty($password)) {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $password]);
            header("Location: login.php?success=1");
            exit();
        } else {
            $error = "모든 필드를 입력하세요.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #d4eaff, #a8d8ea);
            font-family: Arial, sans-serif;
        }

        .container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .container h1 {
            margin-bottom: 20px;
        }

        .container form {
            display: flex;
            flex-direction: column;
        }

        .container input {
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .container button {
            margin-top: 10px;
            padding: 10px;
            font-size: 16px;
            background: #a8d8ea;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        .container button:hover {
            background: #90cbe4;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>로그인 또는 회원가입</h1>
        <form action="" method="POST">
            <input type="text" name="username" placeholder="아이디" required>
            <input type="password" name="password" placeholder="비밀번호" required>
            <button type="submit" name="login">로그인</button>
            <button type="submit" name="register">회원가입</button>
        </form>
        
        <!-- 성공 메시지 출력 -->
        <?php if (isset($_GET['success'])) { ?>
            <div class="success-message">회원가입이 완료되었습니다! 로그인하세요.</div>
        <?php } ?>

        <!-- 에러 메시지 출력 -->
        <?php if (!empty($error)) { ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php } ?>
        
    </div>
</body>
</html>
