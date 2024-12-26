<?php
session_start();
include 'db.php';

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 로그아웃 처리
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// 글 추가 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $imagePath = null;

    // 이미지 업로드 처리
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . uniqid() . '_' . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePath = $targetFilePath;
        }
    }

    if (!empty($title)) {
        $stmt = $conn->prepare("INSERT INTO todos (title, user_id, image_path) VALUES (?, ?, ?)");
        $stmt->execute([$title, $user_id, $imagePath]);
    } else {
        header("Location: ?mess=error");
        exit();
    }
}

// 삭제 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // 이미지 파일 삭제
    $stmt = $conn->prepare("SELECT image_path FROM todos WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    $todo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($todo && $todo['image_path'] && file_exists($todo['image_path'])) {
        unlink($todo['image_path']);
    }

    $stmt = $conn->prepare("DELETE FROM todos WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
}

// 체크박스 상태 변경 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle' && isset($_POST['toggle_id'])) {
    $toggle_id = $_POST['toggle_id'];
    $stmt = $conn->prepare("SELECT checked FROM todos WHERE id = ? AND user_id = ?");
    $stmt->execute([$toggle_id, $user_id]);
    $todo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($todo) {
        $newChecked = $todo['checked'] ? 0 : 1;
        $updateStmt = $conn->prepare("UPDATE todos SET checked = ? WHERE id = ? AND user_id = ?");
        $updateStmt->execute([$newChecked, $toggle_id, $user_id]);
    }
}

// 글 수정 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['edit_id']) && isset($_POST['new_title'])) {
    $edit_id = $_POST['edit_id'];
    $new_title = trim($_POST['new_title']);
    if (!empty($new_title)) {
        $stmt = $conn->prepare("UPDATE todos SET title = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_title, $edit_id, $user_id]);
    }
}

// 현재 사용자 To-Do 리스트 가져오기
$todos = $conn->prepare("SELECT * FROM todos WHERE user_id = ? ORDER BY id DESC");
$todos->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="todo.css">
</head>
<body>
    <!-- 로그아웃 버튼 섹션 -->
    <div class="logout-section">
        <a href="?logout=true" class="logout-button">로그아웃</a>
    </div>

    <div class="main-section">
       <!-- 할 일 추가 섹션 -->
       <div class="add-section">
          <form action="" method="POST" enctype="multipart/form-data" autocomplete="off">
             <input type="hidden" name="action" value="add">
             <?php if (isset($_GET['mess']) && $_GET['mess'] == 'error') { ?>
                <input type="text" name="title" style="border-color: #ff6666" placeholder="필수 입력 항목입니다" />
              <?php } else { ?>
              <input type="text" name="title" placeholder="할 일을 입력하세요" />
              <?php } ?>
              <input type="file" name="image" accept="image/*">
              <button type="submit">추가 &nbsp; <span>&#43;</span></button>
          </form>
       </div>

       <!-- 할 일 목록 섹션 -->
       <div class="show-todo-section">
            <?php if ($todos->rowCount() <= 0) { ?>
                <div class="todo-item">
                    <div class="empty">
                        <p>할 일이 없습니다. 새로운 할 일을 추가하세요!</p>
                        <p class="spacer"></p>
                        <img src="f.png" alt="No tasks image" width="100%" />
                        <img src="Ellipsis.gif" alt="Loading animation" width="80px">
                    </div>
                </div>
            <?php } else { ?>
                <?php while ($todo = $todos->fetch(PDO::FETCH_ASSOC)) { ?>
                    <div class="todo-item">
                        <!-- 체크박스 상태 변경 -->
                        <form action="" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="toggle_id" value="<?php echo $todo['id']; ?>">
                            <input type="checkbox" class="check-box" onchange="this.form.submit()" <?php if ($todo['checked']) echo 'checked'; ?> />
                        </form>

                        <!-- 할 일 제목 -->
                        <h2 class="<?php echo $todo['checked'] ? 'checked' : ''; ?>" style="display: inline;">
                            <?php echo htmlspecialchars($todo['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </h2>

                        <!-- 이미지 표시 -->
                        <?php if (!empty($todo['image_path'])) { ?>
                            <div class="todo-image">
                                <img src="<?php echo htmlspecialchars($todo['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Todo Image" width="100">
                            </div>
                        <?php } ?>

                        <!-- 삭제 및 수정 버튼 -->
                        <div class="button-container">
                            <button type="button" class="edit-button" onclick="enableEdit(this)">✏</button>
                            <form action="" method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="delete_id" value="<?php echo $todo['id']; ?>">
                                <button type="submit" class="remove-to-do">❎</button>
                            </form>
                        </div>
                        <small>생성일: <?php echo date("Y-m-d H:i:s", strtotime($todo['date_time'])); ?></small>

                        <!-- 글 수정 입력 -->
                        <form action="" method="POST" style="margin-top: 10px; display: inline;">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="edit_id" value="<?php echo $todo['id']; ?>">
                            <input type="text" name="new_title" value="<?php echo htmlspecialchars($todo['title'], ENT_QUOTES, 'UTF-8'); ?>" class="edit-input" />
                            <button type="submit" class="save-button">저장</button>
                        </form>
                    </div>
                <?php } ?>
            <?php } ?>
       </div>
    </div>

    <script>
        function enableEdit(button) {
            const todoItem = button.closest('.todo-item');
            const editInput = todoItem.querySelector('.edit-input');
            const saveButton = todoItem.querySelector('.save-button');
            editInput.style.display = 'inline';
            saveButton.style.display = 'inline';
            editInput.focus();
        }
    </script>
</body>
</html>