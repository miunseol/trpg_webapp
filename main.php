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

// 2. 전체 특기 이름 목록 가져오기 (매핑용)
$skill_master_sql = "SELECT * FROM skill_table";
$master_result = mysqli_query($conn, $skill_master_sql);
$skill_names = [];
while($row = mysqli_fetch_assoc($master_result)) {
    $skill_names[$row['skill_id']] = $row['skill_name'];
}
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
        <svg id="mana-layer" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10;">
            <defs>
                <filter id="glow">
                    <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
                    <feMerge>
                        <feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>
            <path id="mana-line" d="" stroke="#f1c40f" stroke-width="2" fill="none" filter="url(#glow)" stroke-dasharray="5,5" />
        </svg>

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

        <div class="skill-table-section">
            <div class="grid-header">
                <div class="dice-label">2D6</div>
                <?php
                $categories = ["별", "짐승", "힘", "노래", "꿈", "어둠"];
                foreach ($categories as $index => $cat_name) {
                    $field_id = $index + 1;
                    // DB에 저장된 전문 분야와 일치하면 'strong' 클래스 부여
                    $is_strong = ($char['strong_field'] == $field_id) ? "strong" : "";
                    echo "<div class='cat $is_strong' data-field='$field_id'>$cat_name</div>";
                    if ($index < 5) echo "<div class='gap'></div>";
                }
                ?>
            </div>

            <?php
            for ($y = 2; $y <= 12; $y++) {
                echo "<div class='grid-row'>";
                echo "<div class='dice-num'>$y</div>";
                
                for ($x = 1; $x <= 11; $x++) {
                    if ($x % 2 != 0) {
                        $col_idx = ($x + 1) / 2;
                        $skill_id = $col_idx * 100 + $y;
                        $name = $skill_names[$skill_id] ?? $skill_id;
                        $is_owned = in_array($skill_id, $my_skills) ? "owned" : "";
                        
                        echo "<div class='skill-cell $is_owned' 
                                   data-x='$x' data-y='$y' 
                                   data-id='$skill_id' 
                                   id='skill-$skill_id'>$name</div>";
                    } else {
                        echo "<div class='gap-cell' data-x='$x' data-y='$y'></div>";
                    }
                }
                echo "</div>";
            }
            ?>
        </div>
    </div> <div id="dice-overlay"></div>

    <script>
        const INITIAL_OWNED_SKILLS = <?php echo json_encode($my_skills); ?>;
        // PHP 데이터를 JS 객체로 변환하여 저장
        const SHEET_CONFIG = {
            charId: <?php echo $char_id; ?>,
            initialstrong: <?php echo $char['strong_field']; ?>,
            initialSkills: <?php echo json_encode($my_skills); ?>
        };
    </script>
    <script src="js/interaction.js"></script>
</body>
</html>