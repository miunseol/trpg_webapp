<?php
header('Content-Type: application/json');

// 업로드 설정
$upload_dir = __DIR__ . '/uploads/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

// 폴더 없으면 생성
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    
    // 에러 체크
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => '업로드 실패']);
        exit;
    }
    
    // 파일 타입 체크
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => '이미지 파일만 업로드 가능합니다.']);
        exit;
    }
    
    // 파일 크기 체크
    if ($file['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => '파일 크기는 5MB 이하만 가능합니다.']);
        exit;
    }
    
    // 파일명 생성 (중복 방지)
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('img_', true) . '.' . $extension;
    $target_path = $upload_dir . $new_filename;
    
    // 파일 이동
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $url = 'uploads/' . $new_filename;
        echo json_encode([
            'success' => true, 
            'url' => $url,
            'message' => '업로드 성공'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '파일 저장 실패']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '잘못된 요청']);
}
?>