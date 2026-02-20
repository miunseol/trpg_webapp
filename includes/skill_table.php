<?php
// includes/skill_table.php
// 특기 테이블 공통 렌더링
//
// 필요 변수 (include 전에 세팅):
//   $skill_names    — skill_id => skill_name 배열 (skill_data.php에서 로드)
//   $table_mode     — 'create' | 'sheet'
//   $my_skills      — 보유 특기 배열 (sheet 모드에서만 필요, 없으면 [])
//   $specialty_field   — 전문 분야 ID (sheet 모드에서만 필요, 없으면 null)

$my_skills = $my_skills ?? [];
$specialty_field = $specialty_field ?? null;
?>

<!-- SVG 마나 레이어 -->
<svg id="mana-layer" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:10;">
    <defs>
        <filter id="glow">
            <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
            <feMerge>
                <feMergeNode in="coloredBlur"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>
    <path id="mana-line" d="" stroke="#f1c40f" stroke-width="2" 
          fill="none" filter="url(#glow)" stroke-dasharray="5,5"/>
</svg>

<!-- 특기 테이블 -->
<div class="skill-table-section">
    <div class="grid-header">
        <div class="dice-label">2D6</div>
        <?php
        $categories = ["별", "짐승", "힘", "노래", "꿈", "어둠"];
        foreach ($categories as $index => $cat_name) {
            $field_id = $index + 1;
            $specialty_class = '';
            if ($table_mode === 'sheet' && $specialty_field == $field_id) {
                $specialty_class = 'specialty';
            }
            echo "<div class='cat $specialty_class' data-field='$field_id'>$cat_name</div>";
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
                $owned_class = in_array($skill_id, $my_skills) ? 'owned' : '';
                
                echo "<div class='skill-cell $owned_class' 
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