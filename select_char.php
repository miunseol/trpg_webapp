<?php
session_start(); // 세션 시작 (로그인 상태 유지용)
$conn = mysqli_connect("localhost", "root", "", "trpg_db");

$char_id = $_GET['id']; // 주소창의 ?id=값 을 가져옴

// 해당 캐릭터 정보 가져오기
$sql = "SELECT * FROM character_sheets WHERE id = $char_id";
$result = mysqli_query($conn, $sql);
$char_data = mysqli_fetch_assoc($result);

if ($char_data) {
    $_SESSION['char_id'] = $char_data['id'];
    $_SESSION['char_name'] = $char_data['name'];
    
    // 선택 완료 후 메인 대시보드로 이동
    header("Location: main.php"); 
} else {
    echo "캐릭터를 찾을 수 없습니다.";
}
?>