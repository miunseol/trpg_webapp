/**
 * [기능 1] 초기 데이터 설정
 */
let ownedSkillIds = [];
let currentSpecialty;
let specialtyFieldX;
let isPreparingRoll = false;
const hasCircle = false;
const hasDictionary = false;

document.addEventListener('DOMContentLoaded', () => {
    // 1. PHP 데이터 로드
    if (typeof SHEET_CONFIG !== 'undefined') {
        charId = SHEET_CONFIG.charId;
        currentSpecialty = SHEET_CONFIG.initialSpecialty;
        specialtyFieldX = (currentSpecialty * 2) - 1;
        ownedSkillIds = SHEET_CONFIG.initialSkills;
    }

    // 2. 브라우저 기본 기능 차단
    document.addEventListener('contextmenu', (e) => {
        if (e.target.closest('.sheet-container')) e.preventDefault();
    }, false);
    document.addEventListener('dragstart', (e) => e.preventDefault());

    // 3. 특기 칸 이벤트 등록
    const skillCells = document.querySelectorAll('.skill-cell');
    skillCells.forEach(cell => {
        cell.addEventListener('mouseenter', handleMouseEnter);
        cell.addEventListener('mouseleave', handleMouseLeave);
        cell.addEventListener('click', handleCellClick);
        cell.addEventListener('mousedown', handleMouseDown);
        cell.addEventListener('mouseup', handleMouseUp);
    });

    // 4. 전문 분야 초기화
    initSpecialtyEvents();
    console.log("[시스템] 캐릭터 데이터 로드 완료:", specialtyFieldX);
});

/**
 * [기능 2] 거리 계산 알고리즘
 */
function getNearestSkillInfo(targetX, targetY) {
    let minDistance = Infinity;
    let nearestIds = [];

    ownedSkillIds.forEach(id => {
        const ox = (Math.floor(id / 100) * 2) - 1;
        const oy = id % 100;

        let dx = Math.abs(targetX - ox);
        let dy = Math.abs(targetY - oy);

        if (hasCircle) {
            dx = Math.min(dx, 12 - dx);
        }

        // 전문 분야 보정
        let finalDx = dx;
        let startX = Math.min(targetX, ox);
        let endX = Math.max(targetX, ox);

        if (startX < specialtyFieldX - 1 && specialtyFieldX - 1 < endX) finalDx--;
        else if (startX < specialtyFieldX + 1 && specialtyFieldX + 1 < endX) finalDx--;

        let totalDist = finalDx + dy;
        if (totalDist < minDistance) {
            minDistance = totalDist;
            nearestIds = [id];
        } else if (totalDist === minDistance) {
            nearestIds.push(id);
        }
    });

    return { 
        distance: minDistance, 
        nearestId: nearestIds.length > 0 ? nearestIds[0] : null,
        difficulty: 5 + (hasDictionary ? Math.round(minDistance / 2) : minDistance)
    };
}

/**
 * [기능 3] 전문 분야 설정
 */
function initSpecialtyEvents() {
    const categories = document.querySelectorAll('.cat');
    
    categories.forEach(cat => {
        cat.addEventListener('click', function() {
            if (document.getElementById('lock-switch').checked) return;
            const fieldId = parseInt(this.dataset.field);
            updateSpecialty(fieldId, this.innerText);
        });
    });
}

function updateSpecialty(fieldId, fieldName) {
    currentSpecialty = fieldId;
    specialtyFieldX = (fieldId * 2) - 1;

    document.querySelectorAll('.cat').forEach(c => c.classList.remove('specialty'));
    const targetCat = document.querySelector(`.cat[data-field="${fieldId}"]`);
    if (targetCat) targetCat.classList.add('specialty');

    saveSpecialtyToServer(fieldId);
    console.log(`[시스템] 전문 분야 변경: ${fieldName} (X좌표: ${specialtyFieldX})`);
}

function saveSpecialtyToServer(fieldId) {
    const data = new URLSearchParams();
    data.append('char_id', SHEET_CONFIG.charId);
    data.append('field_id', fieldId);

    fetch('update_specialty.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.json())
    .then(result => console.log("전문 분야 저장:", result.message))
    .catch(err => console.error("저장 실패:", err));
}

/**
 * [기능 4] 특기 습득/해제
 */
function handleCellClick(e) {
    const lockSwitch = document.getElementById('lock-switch');
    if (lockSwitch && lockSwitch.checked) return;

    const cell = e.currentTarget;
    const skillId = parseInt(cell.dataset.id);

    if (cell.classList.contains('owned')) {
        cell.classList.remove('owned');
        ownedSkillIds = ownedSkillIds.filter(id => id !== skillId);
    } else {
        cell.classList.add('owned');
        ownedSkillIds.push(skillId);
    }
    
    saveSkillsToServer();
}

function saveSkillsToServer() {
    const data = new URLSearchParams();
    data.append('char_id', SHEET_CONFIG.charId);
    data.append('skills', JSON.stringify(ownedSkillIds));
    
    fetch('update_skills.php', {
        method: 'POST',
        body: data
    })
    .then(res => res.json())
    .then(result => console.log("특기 저장:", result.message))
    .catch(err => console.error("저장 실패:", err));
}

/**
 * [기능 5] 마나 선 시각 효과
 */
function handleMouseEnter(e) {
    const cell = e.currentTarget;
    if (cell.classList.contains('owned') || ownedSkillIds.length === 0) return;
    
    const tx = parseInt(cell.dataset.x);
    const ty = parseInt(cell.dataset.y);
    const info = getNearestSkillInfo(tx, ty);
    const sourceCell = document.getElementById(`skill-${info.nearestId}`);
    const line = document.getElementById('mana-line');
    
    if (line) line.classList.remove('active');
    if (sourceCell) drawManaLine(sourceCell, cell);
    
    const diffSpan = document.createElement('span');
    diffSpan.className = 'diff-pop';
    diffSpan.innerText = info.difficulty;
    cell.appendChild(diffSpan);
}

function handleMouseLeave(e) {
    const cell = e.currentTarget;
    const diffPop = cell.querySelector('.diff-pop');
    if (diffPop) diffPop.remove();
    
    const line = document.getElementById('mana-line');
    if (line) line.classList.remove('active');
}

function drawManaLine(startEl, endEl) {
    const svg = document.getElementById('mana-layer');
    const line = document.getElementById('mana-line');
    if (!svg || !line) return;

    const rect = svg.getBoundingClientRect();
    const sRect = startEl.getBoundingClientRect();
    const eRect = endEl.getBoundingClientRect();

    const x1 = sRect.left - rect.left + sRect.width / 2;
    const y1 = sRect.top - rect.top + sRect.height / 2;
    const x2 = eRect.left - rect.left + eRect.width / 2;
    const y2 = eRect.top - rect.top + eRect.height / 2;

    const curveHeight = -Math.max(60, Math.abs(x2 - x1) / 2);
    const cx1 = x1 + (x2 - x1) / 3;
    const cy1 = y1 + curveHeight;
    const cx2 = x1 + 2 * (x2 - x1) / 3;
    const cy2 = y2 + curveHeight;

    line.classList.remove('active');
    line.setAttribute('d', `M ${x1} ${y1} C ${cx1} ${cy1}, ${cx2} ${cy2}, ${x2} ${y2}`);
    void line.offsetWidth;
    line.classList.add('active');
}

/**
 * [기능 6] 주사위 굴림
 */
function handleMouseDown(e) {
    const isLocked = document.getElementById('lock-switch').checked;
    if (!isLocked) return;

    if (e.button === 0) {
        isPreparingRoll = true;
        e.currentTarget.classList.add('preparing');
        
        const line = document.getElementById('mana-line');
        if (line) {
            line.style.strokeWidth = "6px";
            line.style.filter = "drop-shadow(0 0 12px #fff8b0) drop-shadow(0 0 25px #f1c40f)";
        }
    } 
    else if (e.button === 2) {
        if (isPreparingRoll) {
            e.preventDefault();
            resetPreparation(e.currentTarget);
        }
    }
}

function handleMouseUp(e) {
    if (e.button === 0 && isPreparingRoll) {
        const cell = e.currentTarget;
        const tx = parseInt(cell.dataset.x);
        const ty = parseInt(cell.dataset.y);
        const info = getNearestSkillInfo(tx, ty);
        const skillName = cell.innerText.split('\n')[0];

        executeDiceRoll(skillName, info.difficulty);
        resetPreparation(cell);
    }
}

function resetPreparation(cell) {
    isPreparingRoll = false;
    if (cell) cell.classList.remove('preparing');
    
    const line = document.getElementById('mana-line');
    if (line) {
        line.style.strokeWidth = "3px";
        line.style.filter = "drop-shadow(0 0 8px #fff8b0) drop-shadow(0 0 16px #ffe066)";
    }
}

function executeDiceRoll(skillName, difficulty) {
    const d1 = Math.floor(Math.random() * 6) + 1;
    const d2 = Math.floor(Math.random() * 6) + 1;
    const total = d1 + d2;
    const isSuccess = total >= difficulty;

    console.log(`[${skillName}] 판정: ${total} vs ${difficulty} -> ${isSuccess ? '성공' : '실패'}`);
    
    // TODO: 나중에 팝업 UI 구현
    // showDiceResult(d1, d2, total, difficulty, isSuccess);
}