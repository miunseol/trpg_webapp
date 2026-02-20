<?php
session_start();
if (!isset($_SESSION['char_id'])) {
    header("Location: index.php");
    exit;
}

$conn = mysqli_connect("localhost", "root", "", "trpg_db");
$char_id = $_SESSION['char_id'];

// 1. 캐릭터 정보 및 보유 특기 가져오기
$sql = "SELECT * FROM character_sheets WHERE id = $char_id";
$result = mysqli_query($conn, $sql);
$char = mysqli_fetch_assoc($result);
$my_skills = json_decode($char['skills'], true) ?: []; // 예: [102, 305]

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?php echo $char['name']; ?> - 캐릭터 시트</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="sheet-container" style="position: relative;">
        <header class="char-header">
            <h1><?php echo $char['name']; ?> <small>(<?php echo $char['rule_system']; ?>)</small></h1>
            <div class="controls">
                <label class="switch">
                    <input type="checkbox" id="lock-switch" checked>
                    <span class="slider"></span>
                </label>
                <span>편집 잠금</span>
            </div>
        </header>
        <?php
        // 2. 특기 테이블 보여주기
        include 'includes/skill_data.php';
        $table_mode = 'sheet';
        $specialty_field = $char['specialty_field'];
        include 'includes/skill_table.php';
        ?>
    </div> <div id="dice-overlay"></div>

    <script>
        const INITIAL_OWNED_SKILLS = <?php echo json_encode($my_skills); ?>;
        // PHP 데이터를 JS 객체로 변환하여 저장
        const SHEET_CONFIG = {
            charId: <?php echo $char_id; ?>,
            mode: 'sheet',
            maxSkills: 5,
            initialSpecialty: <?php echo $specialty_field; ?>,  // ← initialstrong 버그 수정
            initialSkills: <?php echo json_encode($my_skills); ?>,
            soulSkill: <?php echo json_encode($char['soul_skill'] ?? ''); ?>
        };
    </script>
    <script src="js/interaction.js"></script>
</body>
</html>