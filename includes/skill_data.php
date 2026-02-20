<?php
// includes/skill_data.php
// 특기 테이블 데이터 로드 (공통)

$skill_master_sql = "SELECT * FROM skill_table";
$master_result = mysqli_query($conn, $skill_master_sql);
$skill_names = [];
while($row = mysqli_fetch_assoc($master_result)) {
    $skill_names[$row['skill_id']] = $row['skill_name'];
}