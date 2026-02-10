<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "trpg_db");

// 캐릭터 목록 가져오기
$sql = "SELECT * FROM character_sheets ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$field_names = [1 => "별", 2 => "짐승", 3 => "힘", 4 => "노래", 5 => "꿈", 6 => "어둠"];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRPG 웹앱 - 캐릭터 관리</title>
    <link rel="stylesheet" href="css/index-style.css">
</head>
<body>
    <div class="container">
        <!-- 상단 네비게이션 -->
        <header class="main-header">
            <div class="logo">
                <h1>TRPG 웹앱</h1>
            </div>
            <nav class="tabs">
                <a href="index.php" class="tab active">캐릭터</a>
                <a href="#" class="tab disabled">세션</a>
                <a href="#" class="tab disabled">방 관리</a>
            </nav>
            <div class="user-info">
                <!-- 나중에 로그인 기능 추가 시 사용 -->
                <span class="username">게스트</span>
            </div>
        </header>

        <!-- 캐릭터 목록 섹션 -->
        <section class="character-section">
            <div class="section-header">
                <h2>내 캐릭터</h2>
                <a href="create_char.php" class="btn-create">
                    <span class="icon">+</span> 새 캐릭터
                </a>
            </div>

            <div class="character-grid">
                <?php while($char = mysqli_fetch_assoc($result)): ?>
                <div class="character-card" onclick="location.href='select_char.php?id=<?php echo $char['id']; ?>'">
                    <div class="card-header">
                        <h3 class="char-name"><?php echo htmlspecialchars($char['name']); ?></h3>
                        <span class="rule-badge"><?php echo $char['rule_system']; ?></span>
                    </div>
                    <div class="card-body">
                        <div class="strong-info">
                            <span class="label">전문 분야</span>
                            <span class="strong-badge field-<?php echo $char['strong_field']; ?>">
                                <?php echo $field_names[$char['strong_field']]; ?>
                            </span>
                        </div>
                        <div class="skill-count">
                            <?php 
                            $skills = json_decode($char['skills'], true) ?: [];
                            $skill_count = count($skills);
                            ?>
                            <span class="label">보유 특기</span>
                            <span class="count"><?php echo $skill_count; ?>개</span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="created-date"><?php echo date('Y.m.d', strtotime($char['created_at'])); ?></span>
                    </div>
                </div>
                <?php endwhile; ?>

                <?php if(mysqli_num_rows($result) == 0): ?>
                <div class="empty-state">
                    <p>아직 캐릭터가 없습니다.</p>
                    <a href="create_char.php" class="btn-create-large">첫 캐릭터 만들기</a>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>