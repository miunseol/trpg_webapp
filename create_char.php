<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "trpg_db");

// 전체 특기 이름 목록 가져오기
$skill_master_sql = "SELECT * FROM skill_table";
$master_result = mysqli_query($conn, $skill_master_sql);
$skill_names = [];
while($row = mysqli_fetch_assoc($master_result)) {
    $skill_names[$row['skill_id']] = $row['skill_name'];
}

// 경력 목록 가져오기
$archetypes_sql = "SELECT * FROM archetypes ORDER BY id";
$archetypes_result = mysqli_query($conn, $archetypes_sql);
$archetypes = [];
while($row = mysqli_fetch_assoc($archetypes_result)) {
    $archetypes[] = $row;
}

// 기관 목록 가져오기
$orgs_sql = "SELECT * FROM organs ORDER BY id";
$orgs_result = mysqli_query($conn, $orgs_sql);
$organizations = [];
while($row = mysqli_fetch_assoc($orgs_result)) {
    $organizations[] = $row;
}

// 종족 목록 가져오기 (이단자 전용)
$ancestries_sql = "SELECT * FROM ancestries ORDER BY id";
$ancestries_result = mysqli_query($conn, $ancestries_sql);
$ancestries = [];
while($row = mysqli_fetch_assoc($ancestries_result)) {
    $ancestries[] = $row;
}

// 작위 목록 가져오기 (이종족 전용)
$peerages_sql = "SELECT * FROM peerages ORDER BY id";
$peerages_result = mysqli_query($conn, $peerages_sql);
$peerages = [];
while($row = mysqli_fetch_assoc($peerages_result)) {
    $peerages[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>새 캐릭터 만들기</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/create_char.css">
    
    <!-- Cropper.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
</head>
<body>
    <div class="sheet-container" style="position: relative;">
        <header class="char-header">
            <h1>새 캐릭터 만들기</h1>
            <a href="index.php" style="color: #667eea; text-decoration: none;">← 돌아가기</a>
        </header>

        <!-- 캐릭터 시트 폼 -->
        <div class="create-form">
            <form id="create-char-form" method="POST" action="create_char_process.php">
                <!-- 캐릭터 기본 정보 섹션 -->
                <div class="form-section">
                    <h3>📋 캐릭터 기본 정보</h3>
                    
                    <div class="profile-layout">
                        <!-- 왼쪽: 캐릭터 이미지 & 장서 이미지 -->
                        <div class="profile-left">
                            <!-- 캐릭터 이미지 -->
                            <div class="form-group">
                                <label>캐릭터 이미지</label>
                                <div class="image-upload-area size-large" data-target="image_url">
                                    <div class="upload-placeholder">
                                        <span class="upload-icon">📷</span>
                                        <p>이미지 업로드</p>
                                        <small>1:1 비율로 자동 조정</small>
                                    </div>
                                    <img class="preview-image" style="display: none;">
                                    <input type="file" class="file-input" accept="image/*" style="display: none;">
                                </div>
                                <input type="hidden" name="image_url" id="image_url">
                            </div>
                            
                            <!-- 장서 이미지 -->
                            <div class="form-group">
                                <label>장서 이미지</label>
                                <div class="image-upload-area size-large" data-target="library_image">
                                    <div class="upload-placeholder">
                                        <span class="upload-icon">📚</span>
                                        <p>장서 이미지</p>
                                        <small>1:1 비율</small>
                                    </div>
                                    <img class="preview-image" style="display: none;">
                                    <input type="file" class="file-input" accept="image/*" style="display: none;">
                                </div>
                                <input type="hidden" name="library_image" id="library_image">
                            </div>
                        </div>

                        <!-- 오른쪽: 프로필 정보 -->
                        <div class="profile-right">
                            <!-- 마법명 & 캐릭터 이름 -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label>마법명<span class="required">*</span></label>
                                    <input type="text" name="magic_name" id="magic_name" required placeholder="마법명">
                                </div>
                                <div class="form-group">
                                    <label>캐릭터 이름<span class="required">*</span></label>
                                    <input type="text" name="char_name" id="char_name" required placeholder="이름">
                                </div>
                            </div>
                            
                            <!-- 성별 / 계제 / 공적점 / 마화 -->
                            <div class="compact-row-5">
                                <div class="compact-field">
                                    <label>성별<span class="required">*</span></label>
                                    <select name="gender" id="gender" required>
                                        <option value="">선택</option>
                                        <option value="남">남</option>
                                        <option value="여">여</option>
                                        <option value="무성">무성</option>
                                        <option value="양성">양성</option>
                                        <option value="custom">입력</option>
                                    </select>
                                    <div class="gender-custom" id="gender-custom-input">
                                        <input type="text" name="gender_custom" placeholder="성별">
                                    </div>
                                </div>
                                <div class="compact-field">
                                    <label>계제<span class="required">*</span></label>
                                    <input type="number" name="tier" id="tier" min="0" max="7" value="3" required>
                                </div>
                                <div class="compact-field">
                                    <label>공적점</label>
                                    <input type="number" name="grade_points" id="grade_points" min="0" value="0">
                                </div>
                                <div class="compact-field">
                                    <label>마화</label>
                                    <input type="number" name="mana_currency" id="mana_currency" min="0" value="0">
                                </div>
                                <div class="compact-field">
                                    <label>색상</label>
                                    <input type="color" name="character_color" id="character_color" value="#667eea">
                                </div>
                            </div>

                            <!-- 나이 / 캐릭터 색상 -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label>나이</label>
                                    <input type="text" name="age" id="age" placeholder="예: 외견상 20대지만 사실 300살.">
                                </div>
                                <div class="form-group">
                                    <label>사회적 신분</label>
                                    <input type="text" name="alias_identity" id="alias_identity" placeholder="경찰 / 대학생">
                                </div>
                            </div>

                            <!-- 경력 & 기관 -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label>경력<span class="required">*</span></label>
                                    <select name="archetype_id" id="archetype_id" required>
                                        <option value="">선택하세요</option>
                                        <?php foreach($archetypes as $career): ?>
                                            <option value="<?php echo $career['id']; ?>" 
                                                    data-duty="<?php echo htmlspecialchars($career['duty']); ?>">
                                                <?php echo $career['name_kr']; ?> (<?php echo $career['name_ruby']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="info-text" id="career-duty"></p>
                                </div>
                                <div class="form-group">
                                    <label>소속 기관</label>
                                    <select name="organ_id" id="organ_id">
                                        <option value="">무소속</option>
                                        <?php foreach($organizations as $org): ?>
                                            <option value="<?php echo $org['id']; ?>"
                                                    data-duty="<?php echo htmlspecialchars($org['duty']); ?>">
                                                <?php echo $org['name_kr']; ?> (<?php echo $org['name_ruby']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="info-text" id="org-duty"></p>
                                </div>
                            </div>

                            <!-- 전투 스탯 -->
                            <div class="stats-row">
                                <div class="stat-box">
                                    <label>공격력<span class="required">*</span></label>
                                    <input type="number" name="attack_point" id="attack_point" min="0" max="7" value="0" required>
                                </div>
                                <div class="stat-box">
                                    <label>방어력<span class="required">*</span></label>
                                    <input type="number" name="defense_point" id="defense_point" min="0" max="7" value="0" required>
                                </div>
                                <div class="stat-box">
                                    <label>근원력<span class="required">*</span></label>
                                    <input type="number" name="principal_point" id="principal_point" min="0" max="7" value="0" required>
                                </div>
                            </div>
                            <p class="info-text">💡 기관에 따라 특정 스탯이 가장 높아야 하는 조건이 있을 수 있습니다.</p>
                        </div>
                    </div>

                    <!-- 메모 (전체 폭) -->
                    <div class="form-group full-width">
                        <label>메모 (선택)</label>
                        <textarea name="memo" id="memo" placeholder="캐릭터에 대한 메모를 입력하세요"></textarea>
                    </div>
                </div>

                <!-- 경력 & 소속 섹션 -->
                <div class="form-section">
                    <h3>🎭 경력 & 소속</h3>
                    
                    <!-- 조건부: 이단자 선택 시 종족 (선택) -->
                    <div class="form-row conditional-field" id="race-section">
                        <div class="form-group">
                            <label>종족 (이단자 전용 - 선택)</label>
                            <select name="ancestry_id" id="ancestry_id">
                                <option value="">이종족 사용 안 함</option>
                                <?php foreach($races as $race): ?>
                                    <option value="<?php echo $race['id']; ?>"
                                            data-duty="<?php echo htmlspecialchars($race['duty']); ?>">
                                        <?php echo $race['name_kr']; ?> (<?php echo $race['name_ruby']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="info-text" id="race-duty">황혼선서 추가 룰</p>
                        </div>

                        <!-- 조건부: 종족 선택 시 작위 (필수) -->
                        <div class="form-group conditional-field" id="title-section">
                            <label>작위 (이종족 전용)<span class="required">*</span></label>
                            <select name="peerage_id" id="peerage_id">
                                <option value="">선택하세요</option>
                                <?php foreach($peerages as $title): ?>
                                    <option value="<?php echo $title['id']; ?>">
                                        <?php echo $title['title_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="info-text">종족의 위계를 나타냅니다.</p>
                        </div>
                    </div>
                </div>

                <!-- 진정한 모습 섹션 -->
                <div class="form-section">
                    <h3>✨ 진정한 모습 (선택)</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>진정한 모습 이름</label>
                            <input type="text" name="true_form_name" id="true_form_name" placeholder="예: 불타는 날개">
                        </div>

                        <!-- 진정한 모습 이미지 (중간) -->
                        <div class="form-group">
                            <label>진정한 모습 이미지</label>
                            <div class="image-upload-area size-medium" data-target="true_form_image">
                                <div class="upload-placeholder">
                                    <span class="upload-icon">✨</span>
                                    <p>진정한 모습</p>
                                    <small>1:1 비율</small>
                                </div>
                                <img class="preview-image" style="display: none;">
                                <input type="file" class="file-input" accept="image/*" style="display: none;">
                            </div>
                            <input type="hidden" name="true_form_image" id="true_form_image">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>진정한 모습 효과</label>
                        <textarea name="true_form_effect" id="true_form_effect" placeholder="진정한 모습의 효과를 입력하세요. 예: 전투 중 1회, 【공격력】+2"></textarea>
                        <p class="info-text">진정한 모습의 특수 효과나 능력을 설명하세요.</p>
                    </div>

                    <div class="form-row">
                        <!-- 주권 이미지 (중간) -->
                        <div class="form-group">
                            <label>주권 이미지</label>
                            <div class="image-upload-area size-medium" data-target="sovereignty_image">
                                <div class="upload-placeholder">
                                    <span class="upload-icon">👑</span>
                                    <p>주권 이미지</p>
                                    <small>1:1 비율</small>
                                </div>
                                <img class="preview-image" style="display: none;">
                                <input type="file" class="file-input" accept="image/*" style="display: none;">
                            </div>
                            <input type="hidden" name="sovereignty_image" id="sovereignty_image">
                        </div>

                        <!-- 주권 BGM URL -->
                        <div class="form-group">
                            <label>주권 BGM URL</label>
                            <input type="text" name="sovereignty_bgm" id="sovereignty_bgm" placeholder="https://example.com/bgm.mp3">
                            <p class="info-text">마법사의 테마 음악</p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="strong_field" id="strong_field" value="">
                <input type="hidden" name="skills" id="skills" value="[]">
            </form>
        </div>

        <div class="skill-limit-notice">
            <strong>전문 분야 선택:</strong> 상단 카테고리(별/짐승/힘/노래/꿈/어둠)를 클릭하세요<br>
            <strong>초기 특기 선택:</strong> 특기 칸을 클릭하여 6개를 선택하세요 (<span id="skill-count">0</span>/6)
        </div>

        <svg id="mana-layer" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10;">
            <path id="mana-line" d="" stroke="#f1c40f" stroke-width="2" fill="none" stroke-dasharray="5,5" />
        </svg>

        <div class="skill-table-section">
            <div class="grid-header">
                <div class="dice-label">2D6</div>
                <?php
                $categories = ["별", "짐승", "힘", "노래", "꿈", "어둠"];
                foreach ($categories as $index => $cat_name) {
                    $field_id = $index + 1;
                    echo "<div class='cat' data-field='$field_id'>$cat_name</div>";
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
                        
                        echo "<div class='skill-cell' 
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

        <div class="create-form">
            <p class="error-message" id="error-message"></p>
            <button type="button" class="btn-submit" onclick="validateAndSubmit()">캐릭터 생성</button>
        </div>
    </div>

    <script src="image-upload.js"></script>
    <script src="js/create_char.js"></script>
</body>
</html>