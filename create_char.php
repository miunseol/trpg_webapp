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

    <!-- Google Fonts 불러오기 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Cormorant+Garamond:ital,wght@0,400;1,600&family=UnifrakturMaguntia&display=swap" rel="stylesheet">
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
                <!-- 캐릭터 프로필 정보 섹션 -->
                <div class="form-section">
                    <h3>📋 캐릭터 기본 정보</h3>
                    <!-- 프로필 레이아웃 -->
                    <div class="profile-layout">
                        <!-- 왼쪽: 캐릭터 이미지 & 장서 이미지 -->
                        <div class="profile-left">
                            <div class="compact-field">
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
                            <div class="compact-field">
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
                            <div class="compact-row char-name-row">
                                <div class="compact-field">
                                    <label>마법명<span class="required">*</span></label>
                                    <input type="text" name="magic_name" id="magic_name" required placeholder="마법명">
                                </div>
                                <div class="compact-field">
                                    <label>캐릭터 이름<span class="required">*</span></label>
                                    <input type="text" name="char_name" id="char_name" required placeholder="이름">
                                </div>
                            </div>
                            
                            <!-- 성별 / 나이 / 키 / 색상 -->
                            <div class="compact-row outlook-row">
                                <!-- 성별 -->
                                <div class="compact-field gender-field-wrapper">
                                    <label>성별<span class="required">*</span></label>
                                    <div class="gender-group">
                                        <select name="gender" id="gender" required>
                                            <option value="">선택</option>
                                            <option value="남">남</option>
                                            <option value="여">여</option>
                                            <option value="무성">무성</option>
                                            <option value="양성">양성</option>
                                            <option value="custom">직접 입력</option>
                                        </select>
                                        <input type="text" name="gender_custom" id="gender_custom" placeholder="성별">
                                    </div>
                                </div>
                                <!-- 나이 -->
                                <div class="compact-field">
                                    <label>나이</label>
                                    <input type="text" name="age" id="age" style="width: 190px" placeholder="300살먹은 20대 외모">
                                </div>
                                <!-- 키 -->
                                <div class="compact-field">
                                    <label>키</label>
                                    <input type="text" name="height" id="height" style="width: 60px">
                                </div>
                                <!-- 캐릭터 퍼스널 컬러 -->
                                <div class="compact-field">
                                    <label>색상</label>
                                    <input type="color" name="character_color" id="character_color" value="#667eea">
                                </div>
                            </div>

                            <!-- 사회적 신분 / 활동 거점 -->
                            <div class="compact-row">
                                <div class="compact-field">
                                    <label>사회적 신분</label>
                                    <input type="text" name="alias_identity" id="alias_identity" placeholder="경찰 / 대학생">
                                </div>
                                <div class="compact-field">
                                    <label>활동 거점</label>
                                    <input type="text" name="base_of_operations" id="base_of_operations" placeholder="뉴욕 뉴올리언스">
                                </div>                                
                            </div>

                            <!-- 경력 & 기관 -->
                            <div class="compact-row">
                                <div class="compact-field">
                                    <label>경력<span class="required">*</span></label>
                                    <select name="archetype_id" id="archetype_id" required>
                                        <option value="">선택하세요</option>
                                        <?php foreach($archetypes as $career): ?>
                                            <option value="<?php echo $career['id']; ?>">
                                                <?php echo $career['name_kr']; ?> (<?php echo $career['name_ruby']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="info-text" id="career-duty"></p>
                                </div>
                                <div class="compact-field">
                                    <label>소속 기관</label>
                                    <select name="organ_id" id="organ_id">
                                        <option value="">무소속</option>
                                        <?php foreach($organizations as $org): ?>
                                            <option value="<?php echo $org['id']; ?>">
                                                <?php echo $org['name_kr']; ?> (<?php echo $org['name_ruby']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="info-text" id="org-duty"></p>
                                </div>
                            </div>

                            <!-- 이종족 및 백스토리 -->
                            <div class="compact-column">
                                <div class="compact-row">
                                    <!-- 조건부: 경력 이단자 선택 시 종족 활성화 -->
                                    <div class="compact-field" id="ancestry-peerage-column">
                                        <label>종족 (이단자 전용 - 선택)</label>
                                        <select name="ancestry_id" id="ancestry_id">
                                            <option value="">이종족 사용 안 함</option>
                                            <?php foreach($ancestries as $ancestry): ?>
                                                <option value="<?php echo $ancestry['id']; ?>"
                                                        data-duty="<?php echo htmlspecialchars($ancestry['duty']); ?>">
                                                    <?php echo $ancestry['name_kr']; ?> (<?php echo $ancestry['name_ruby']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="info-text" id="peerage-duty">황혼선서 추가 룰</p>
                                    </div>
                                    <!-- peerage-section은 JS에서 조건을 만족할 때만 동적으로 삽입됨 -->
                                    <script>
                                    // PHP peerages 배열을 JS로 전달
                                    window.PEERAGES = <?php echo json_encode($peerages, JSON_UNESCAPED_UNICODE); ?>;
                                    </script>
                                </div>

                                <!-- 백스토리 -->                            
                                <div class="compact-field textarea-group">
                                    <label>백스토리</label>
                                    <textarea name="backstory" id="backstory" placeholder="캐릭터는 대법전에서 어떤 입지를 가지는가&#10;캐릭터는 어떤 성격인가&#10;캐릭터는 어떤 과거를 겪었는가"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 캐릭터 데이터 정보 섹션 -->
                <div class="form-section">
                    <h3>🧙‍♂️ 캐릭터 데이터 정보</h3>

                    <!-- 스탯 입력 필드 그룹 -->
                    <div class="profile-layout">
                        <!-- 스탯 원형 입력 필드 -->
                        <div class="magic-circle-container">
                            <svg class="magic-ring" viewBox="0 0 200 200">
                                <defs>
                                    <!-- 룬 문자 경로 정의 (원형 텍스트용) -->
                                    <path id="textCircle" d="M 20,100 A 80,80 0 1,1 180,100 A 80,80 0 1,1 20,100" fill="none" />
                                    <!-- 그라데이션 효과 -->
                                    <radialGradient id="magicGlow" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                                        <stop offset="0%" stop-color="#f1c40f" stop-opacity="0.3" />
                                        <stop offset="100%" stop-color="#f1c40f" stop-opacity="0" />
                                    </radialGradient>
                                </defs>

                                <!-- 1. 배경: 은은한 광원 -->
                                <circle cx="100" cy="100" r="60" fill="url(#magicGlow)" />

                                <!-- 2. 레이어 1: 육망성 (시계 방향 회전) -->
                                <g class="spin-slow">
                                    <polygon points="100,20 170,140 30,140" fill="none" stroke="#667eea" stroke-width="1" opacity="0.4" />
                                    <polygon points="100,180 30,60 170,60" fill="none" stroke="#667eea" stroke-width="1" opacity="0.4" />
                                    <circle cx="100" cy="100" r="85" fill="none" stroke="#667eea" stroke-width="1" stroke-dasharray="2, 4" opacity="0.3" />
                                </g>

                                <!-- 3. 레이어 2: 룬 문자 링 (반시계 방향 회전) -->
                                <g class="spin-reverse">
                                    <circle cx="100" cy="100" r="92" fill="none" stroke="#667eea" stroke-width="1" opacity="0.6"/>
                                    <text fill="#667eea" font-size="10" font-family="monospace" letter-spacing="3" opacity="0.8">
                                        <textPath href="#textCircle" startOffset="0%">
                                            EGO ET TU • FABULA QUAE TRAGOEDIAM PERDIT • MAGIA • LOGIA • VERITAS • UMBRA • LUX •
                                        </textPath>
                                    </text>
                                </g>

                                <!-- 4. 레이어 3: 스탯 연결 역삼각형 (고정 - 입력칸이랑 위치 맞춰야 하니까) -->
                                <g class="static-frame">
                                    <!-- 메인 역삼각형 -->
                                    <polygon points="33,60 167,60 100,177" fill="none" stroke="#f1c40f" stroke-width="2" filter="drop-shadow(0 0 2px #f1c40f)" />
                                    <!-- 장식용 작은 원들 (꼭지점) -->
                                    <circle cx="33" cy="60" r="3" fill="#f1c40f" />
                                    <circle cx="167" cy="60" r="3" fill="#f1c40f" />
                                    <circle cx="100" cy="177" r="3" fill="#f1c40f" />
                                </g>

                                <!-- 5. 중앙 장식 (계제) -->
                                <circle cx="100" cy="100" r="30" fill="rgba(26, 28, 35, 0.9)" stroke="#f1c40f" stroke-width="1.5" />
                                <circle cx="100" cy="100" r="26" fill="none" stroke="#667eea" stroke-width="1" opacity="0.5" />
                            </svg>

                            <!-- 입력 필드 (해솔이 잡은 위치 그대로!) -->
                            <div class="stat-input-group center">
                                <label>계제</label>
                                <input type="number" name="tier" id="tier" min="0" max="7" value="3" class="hex-input" required>
                            </div>
                            <div class="stat-input-group top-left">
                                <label>공격</label>
                                <input type="number" name="attack_point" id="attack_point" min="0" max="7" value="3" class="circle-input" required>
                            </div>
                            <div class="stat-input-group top-right">
                                <label>방어</label>
                                <input type="number" name="defense_point" id="defense_point" min="0" max="7" value="3" class="circle-input" required>
                            </div>
                            <div class="stat-input-group bottom">
                                <label>근원</label>
                                <input type="number" name="principal_point" id="principal_point" min="0" max="7" value="3" class="circle-input" required>
                            </div>
                        </div>
                        <!-- 공적점 및 마화 입력 필드 -->
                        <div class="compact-row">
                            <div class="compact-field">
                                <label>공적점</label>
                                <input type="number" name="grade_points" id="grade_points" min="0" value="0">
                            </div>
                            <div class="compact-field">
                                <label>마화</label>
                                <input type="number" name="mana_currency" id="mana_currency" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- 진정한 모습 & 주권 섹션 -->
                    <h3>✨ 진정한 모습 & 주권</h3>
                    <div class="magic-profile-layout">
                            <!-- [왼쪽] 진정한 모습 영역 -->
                            <div class="magic-left">
                                <!-- 진정한 모습 이미지 -->
                                <div class="form-group center-align">
                                    <label>진정한 모습</label>
                                    <div class="image-upload-area size-medium" data-target="true_form_image">
                                        <div class="upload-placeholder">
                                            <span class="upload-icon">✨</span>
                                            <p>이미지 업로드</p>
                                        </div>
                                        <img class="preview-image" style="display: none;">
                                        <input type="file" class="file-input" accept="image/*" style="display: none;">
                                    </div>
                                    <input type="hidden" name="true_form_image" id="true_form_image" placeholder="진정한 모습의 이름">
                                </div>
                                <!-- 진정한 모습 효과 -->
                                 <div class="form-group">
                                    <label>진정한 모습 효과</label>
                                    
                                    <!-- 1. 효과 선택 드롭다운 -->
                                    <select name="true_form_type" id="true_form_type" style="margin-bottom: 10px;">
                                        <option value="">-- 효과 선택 --</option>
                                        <option value="atk">공격 강화</option>
                                        <option value="def">방어 강화</option>
                                        <option value="origin">근원 강화</option>
                                        <option value="mana">마력 강화</option>
                                        <option value="revive">소생</option>
                                        <option value="custom">직접 입력 (하우스 룰 등)</option>
                                    </select>

                                    <!-- 2. 효과 설명 보여주는 박스 (평소엔 여기 뜸) -->
                                    <div id="effect-description" class="info-box">
                                        진정한 모습의 효과를 선택하면 여기에 설명이 나타납니다.
                                    </div>

                                    <!-- 3. 직접 입력용 텍스트 영역 (평소엔 숨김) -->
                                    <textarea 
                                        name="true_form_effect_custom" 
                                        id="true_form_effect_custom" 
                                        class="simple-textarea" 
                                        style="display: none;" 
                                        placeholder="효과를 직접 입력하세요."></textarea>
                                        
                                    <!-- 실제 DB로 넘어갈 값 (JS가 채워줌) -->
                                    <input type="hidden" name="true_form_effect" id="true_form_effect">
                                </div>
                            </div>
                            <!-- [가운데] 구분선 -->
                            <div class="magic-center">
                                <div class="divider-vertical"></div>
                            </div>
                            <!-- [오른쪽] 주권 영역 -->
                            <div class="magic-right">
                                <!-- 주권 이미지 -->
                                <div class="compact-field center-align">
                                    <label>주권 (영역)</label>
                                    <div class="image-upload-area size-medium" data-target="sovereignty_image">
                                        <div class="upload-placeholder">
                                            <span class="upload-icon">👑</span>
                                            <p>이미지 업로드</p>
                                        </div>
                                        <img class="preview-image" style="display: none;">
                                        <input type="file" class="file-input" accept="image/*" style="display: none;">
                                    </div>
                                </div>
                                <!-- 클래스 재사용: backstory-group -> floating-textarea-group 으로 이름만 바꿔서 공통화 추천 -->
                                <div class="compact-field textarea-group">
                                    <label>주권 묘사</label>
                                    <textarea name="sovereignty_desc" id="sovereignty_desc" placeholder="주권의 형태와 연출을 묘사하세요."></textarea>
                                </div>

                                <!-- 3. 주권 BGM -->
                                <div class="compact-field">
                                    <label>주권 BGM URL</label>
                                    <input type="text" name="sovereignty_bgm" id="sovereignty_bgm" placeholder="Youtube 또는 MP3 링크">
                                </div>
                            </div>

                            <!-- [오른쪽] 정보 입력 영역 -->
                            <div class="magic-right">
                                <!-- 1. 진정한 모습 이름 -->
                                <div class="form-group">
                                    <label>진정한 모습 이름</label>
                                    <input type="text" name="true_form_name" id="true_form_name" placeholder="예: 불타는 날개, 심연의 눈">
                                </div>

                                <!-- 2. 진정한 모습 효과 (플로팅 적용!) -->

                            </div>
                        </div>
                </div>


                <input type="hidden" name="specialty_field" id="specialty_field" value="">
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

    <script src="js/image-upload.js"></script>
    <script src="js/create_char.js"></script>
</body>
</html>