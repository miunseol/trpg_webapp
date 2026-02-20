<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "trpg_db");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 기본 정보
    $char_name = trim($_POST['char_name']);
    $magic_name = trim($_POST['magic_name']);
    $gender = $_POST['gender'];
    $tier = intval($_POST['tier']);
    
    // 성별 직접입력 처리
    if ($gender === 'custom') {
        $gender = trim($_POST['gender_custom']);
    }
    
    // 추가 정보(나이 / 캐릭터 퍼스널 컬러)
    $age = !empty($_POST['age']) ? trim($_POST['age']) : null;
    $user_color = !empty($_POST['character_color']) ? trim($_POST['character_color']) : null;
    $alias_identity = !empty($_POST['alias_identity']) ? trim($_POST['alias_identity']) : null;

    // 백스토리
    $backstory = !empty($_POST['backstory']) ? trim($_POST['backstory']) : null;
    
    // 경력 & 소속
    $archetype_id = intval($_POST['archetype_id']);
    $organ_id = !empty($_POST['organ_id']) ? intval($_POST['organ_id']) : null;
    
    // 종족 & 작위 (이단자 전용)
    $ancestry_id = !empty($_POST['ancestry_id']) ? intval($_POST['ancestry_id']) : null;
    $peerage_id = !empty($_POST['peerage_id']) ? intval($_POST['peerage_id']) : null;
    
    // 전투 스탯
    $attack_point = intval($_POST['attack_point']);
    $defense_point = intval($_POST['defense_point']);
    $principal_point = intval($_POST['principal_point']);

    // 성장 요소
    $grade_points = !empty($_POST['grade_points']) ? intval($_POST['grade_points']) : 0;
    $mana_currency = !empty($_POST['mana_currency']) ? intval($_POST['mana_currency']) : 0;
    
    // 앵커
    for ($i = 1; $i <= 10; $i++) {
        $anchor[$i] = !empty($_POST["anchor_$i"]) ? trim($_POST["anchor_$i"]) : null;
    }

    // 특기 & 전문분야
    $specialty_field  = intval($_POST['specialty_field']);
    $skills = $_POST['skills']; // JSON 문자열

    // 혼의 특기
    $soul_skill = !empty($_POST['soul_skill']) ? trim($_POST['soul_skill']) : null;
    
    // 이미지 & 비주얼
    $image_url = !empty($_POST['image_url']) ? trim($_POST['image_url']) : null;
    $library_image = !empty($_POST['library_image']) ? trim($_POST['library_image']) : null;
    $sovereignty_image = !empty($_POST['sovereignty_image']) ? trim($_POST['sovereignty_image']) : null;
    $sovereignty_bgm = !empty($_POST['sovereignty_bgm']) ? trim($_POST['sovereignty_bgm']) : null;
    
    // 진정한 모습
    $true_form_name = !empty($_POST['true_form_name']) ? trim($_POST['true_form_name']) : null;
    $true_form_image = !empty($_POST['true_form_image']) ? trim($_POST['true_form_image']) : null;
    $true_form_effect = !empty($_POST['true_form_effect']) ? trim($_POST['true_form_effect']) : null;
    
    // 유효성 검사
    if (empty($char_name) || empty($magic_name) || empty($gender)) {
        die('필수 항목을 모두 입력해주세요.');
    }
    
    if ($specialty_field < 1 || $specialty_field > 6) {
        die('전문 분야를 선택해주세요.');
    }
    
    if ($archetype_id < 1 || $archetype_id > 6) {
        die('경력을 선택해주세요.');
    }
    
    // 이단자(archetype_id = 5) + 이종족 선택 시에만 작위 필수
    if ($archetype_id == 5 && !empty($ancestry_id)) {
        if (empty($peerage_id)) {
            die('이종족을 선택한 경우 작위 선택이 필수입니다.');
        }
    }
    
    if ($tier < 0 || $tier > 7) {
        die('계제는 0~7 사이여야 합니다.');
    }
    
    // 특기 개수 확인
    $skills_array = json_decode($skills, true);
    if (!is_array($skills_array) || count($skills_array) !== 5) {
        die('특기는 정확히 5개를 선택해야 합니다.');
    }
    
    // DB 삽입
    $sql = "INSERT INTO character_sheets (
        name, magic_name, gender, age, user_color, 
        tier, archetype_id, organ_id,
        ancestry_id, peerage_id,
        attack_point, defense_point, principal_point,
        grade_points, mana_currency,
        rule_system, specialty_field, skills, 
        magic_power_current, magic_power_max, backstory,
        image_url, library_image, sovereignty_image, sovereignty_bgm,
        true_form_name, true_form_image, true_form_effect, soul_skill
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'MagicaLogia', ?, ?, 4, 4, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // ========== 이 두 줄 추가 ==========
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        die('SQL 에러: ' . mysqli_error($conn));
    }
    // ====================================

    mysqli_stmt_bind_param($stmt, "sssssiiiiiiiiiisssssssssss",
    //                              1234567890123456789012345 6 = 26자
        $char_name,           // 1  s
        $magic_name,          // 2  s
        $gender,              // 3  s
        $age,                 // 4  s
        $user_color,          // 5  s
        $tier,                // 6  i
        $archetype_id,        // 7  i
        $organ_id,            // 8  i
        $ancestry_id,         // 9  i
        $peerage_id,          // 10 i
        $attack_point,        // 11 i
        $defense_point,       // 12 i
        $principal_point,     // 13 i
        $grade_points,        // 14 i
        $mana_currency,       // 15 i
        $specialty_field,     // 16 i
        $skills,              // 17 s
        $backstory,           // 18 s
        $image_url,           // 19 s
        $library_image,       // 20 s
        $sovereignty_image,   // 21 s
        $sovereignty_bgm,     // 22 s
        $true_form_name,      // 23 s
        $true_form_image,     // 24 s
        $true_form_effect,    // 25 s
        $soul_skill           // 26 s
    );

    // 캐릭터 생성 성공 후
    if (mysqli_stmt_execute($stmt)) {
        $new_char_id = mysqli_insert_id($conn);

        // ===== 앵커 저장 =====
        $anchor_sql = "INSERT INTO anchors (char_id, anchor_name) VALUES (?, ?)";
        $anchor_stmt = mysqli_prepare($conn, $anchor_sql);



        for ($i = 1; $i <= 10; $i++) {
            if (empty($anchor[$i])) continue;
            mysqli_stmt_bind_param($anchor_stmt, "is", $new_char_id, $anchor[$i]);
            mysqli_stmt_execute($anchor_stmt);
        }
        
        mysqli_stmt_close($anchor_stmt);
        // ===== 앵커 저장 끝 =====

        $_SESSION['char_id'] = $new_char_id;
        $_SESSION['char_name'] = $char_name;
        header("Location: main.php");
    } else {
        die('캐릭터 생성에 실패했습니다. 다시 시도해주세요.');
    }
}

mysqli_close($conn);
?>