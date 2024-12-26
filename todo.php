<?php 
include 'db.php';

// 글 추가 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    if (!empty($title)) {
        $stmt = $conn->prepare("INSERT INTO todos (title) VALUES (?)");
        $stmt->execute([$title]);
    } else {
        header("Location: ?mess=error");
        exit();
    }
}

// 삭제 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM todos WHERE id = ?");
    $stmt->execute([$delete_id]);
}

// 체크박스 상태 변경 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle' && isset($_POST['toggle_id'])) {
    $toggle_id = $_POST['toggle_id'];
    $stmt = $conn->prepare("SELECT checked FROM todos WHERE id = ?");
    $stmt->execute([$toggle_id]);
    $todo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($todo) {
        $newChecked = $todo['checked'] ? 0 : 1;
        $updateStmt = $conn->prepare("UPDATE todos SET checked = ? WHERE id = ?");
        $updateStmt->execute([$newChecked, $toggle_id]);
    }
}

// 글 수정 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['edit_id']) && isset($_POST['new_title'])) {
    $edit_id = $_POST['edit_id'];
    $new_title = trim($_POST['new_title']);
    if (!empty($new_title)) {
        $stmt = $conn->prepare("UPDATE todos SET title = ? WHERE id = ?");
        $stmt->execute([$new_title, $edit_id]);
    }
}

$todos = $conn->query("SELECT * FROM todos ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>To-Do List</title>
    <link rel="stylesheet" href="todo.css">
</head>
<body>
    <div class="main-section">
       <div class="add-section">
          <form action="" method="POST" autocomplete="off">
             <input type="hidden" name="action" value="add">
             <?php if(isset($_GET['mess']) && $_GET['mess'] == 'error'){ ?>
                <input type="text" 
                     name="title" 
                     style="border-color: #ff6666"
                     placeholder="This field is required" />
              <?php }else{ ?>
              <input type="text" 
                     name="title" 
                     placeholder="What do you need to do?" />
              <?php } ?>
              <button type="submit">Add &nbsp; <span>&#43;</span></button>
          </form>
       </div>
       <div class="show-todo-section">
            <?php if($todos->rowCount() <= 0){ ?>
                <div class="todo-item">
                    <div class="empty">
                        <img src="f.png" width="100%" />
                        <img src="Ellipsis.gif" width="80px">
                    </div>
                </div>
            <?php } ?>

            <?php while($todo = $todos->fetch(PDO::FETCH_ASSOC)) { ?>
                <div class="todo-item">
                    <!-- 체크박스 상태 변경을 위한 폼 -->
                    <form action="" method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="toggle_id" value="<?php echo $todo['id']; ?>">
                        <input type="checkbox" 
                               name="checkbox"
                               class="check-box"
                               onchange="this.form.submit()" 
                               <?php if ($todo['checked']) echo 'checked'; ?> />
                    </form>

                    <h2 class="<?php echo $todo['checked'] ? 'checked' : ''; ?>" style="display: inline;">
                        <?php echo htmlspecialchars($todo['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </h2>

                    <div class="button-container">
                       <!-- 수정 기능을 위한 버튼 -->
                      <button type="button" class="edit-button" onclick="enableEdit(this)">✏</button>

                      <!-- 삭제 기능을 위한 폼 -->
                      <form action="" method="POST" class="delete-form">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="delete_id" value="<?php echo $todo['id']; ?>">
                          <button type="submit" class="remove-to-do">❎</button>
                      </form>
                  </div>

                  <small>created: <?php echo $todo['date_time'] ?></small>

                    <!-- 글 수정 기능을 위한 폼 -->
                    <form action="" method="POST" style="margin-top: 10px; display: inline;">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="edit_id" value="<?php echo $todo['id']; ?>">
                        <input type="text" name="new_title" value="<?php echo htmlspecialchars($todo['title'], ENT_QUOTES, 'UTF-8'); ?>" class="edit-input" />
                        <button type="submit" class="save-button">Save</button>
                    </form>
                </div>
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
