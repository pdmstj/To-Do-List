<?php 

$sName = "localhost"; // 서버 이름
$uName = "root"; // 사용자 이름
$pass = "111111"; // MySQL 비밀번호 (로컬에서 사용하는 비밀번호)
$db_name = "todo"; // 데이터베이스 이름

try {
    // 데이터베이스에 연결 및 UTF-8 인코딩 설정 추가
    $conn = new PDO("mysql:host=$sName;dbname=$db_name;charset=utf8mb4", $uName, $pass);
    // 오류 모드를 예외 처리로 설정
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit(); // 오류 발생 시 실행 중단
}
?>
