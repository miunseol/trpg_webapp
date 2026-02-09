<?php
session_start();
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "trpg_db");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $char_id = $_POST['char_id'];
    $field_id = $_POST['field_id'];
    
    $sql = "UPDATE character_sheets SET specialty_field = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $field_id, $char_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => '전문 분야 저장 완료']);
    } else {
        echo json_encode(['success' => false, 'message' => '저장 실패']);
    }
    
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);
?>