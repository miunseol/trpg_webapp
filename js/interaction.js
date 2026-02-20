let ownedSkillIds = [];
let currentSpecialty = null;
let specialtyFieldX = null;
let isPreparingRoll = false;
const hasCircle = false;
const hasDictionary = false;

// 모드 시스템 — SHEET_CONFIG에서 읽어옴
let MODE = 'sheet';           // 기본값
let MAX_SKILLS = 5;           // 마기카로기아 기본 특기 수

document.addEventListener('DOMContentLoaded', () => {
    // 1. 모드 & 설정 로드
    if (typeof SHEET_CONFIG !== 'undefined') {
        MODE = SHEET_CONFIG.mode || 'sheet';
        MAX_SKILLS = SHEET_CONFIG.maxSkills || 5;

        if (MODE === 'sheet') {
            charId = SHEET_CONFIG.charId;
            currentSpecialty = SHEET_CONFIG.initialSpecialty;
            specialtyFieldX = (currentSpecialty * 2) - 1;
            ownedSkillIds = SHEET_CONFIG.initialSkills || [];
        }
        // create 모드에서는 빈 상태로 시작
    }

    // 2. 브라우저 기본 기능 차단 (양쪽 공통)
    document.addEventListener('contextmenu', (e) => {
        if (e.target.closest('.sheet-container')) e.preventDefault();
    }, false);
    document.addEventListener('dragstart', (e) => e.preventDefault());

    // 3. 특기 칸 이벤트 등록 (양쪽 공통)
    const skillCells = document.querySelectorAll('.skill-cell');
    skillCells.forEach(cell => {
        cell.addEventListener('mouseenter', handleMouseEnter);
        cell.addEventListener('mouseleave', handleMouseLeave);
        cell.addEventListener('click', handleCellClick);

        // 주사위 굴림 이벤트는 sheet 모드에서만
        if (MODE === 'sheet') {
            cell.addEventListener('mousedown', handleMouseDown);
            cell.addEventListener('mouseup', handleMouseUp);
        }
    });

    // 4. 전문 분야 이벤트 (양쪽 공통)
    initSpecialtyEvents();

    console.log(`[시스템] 모드: ${MODE}, 초기화 완료`);
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
            // sheet 모드에서는 전문 분야 변경 비활성화 (판정만)
            if (MODE === 'sheet') return;

            const fieldId = parseInt(this.dataset.field);
            
            // 공통: 시각적 업데이트
            document.querySelectorAll('.cat').forEach(c => c.classList.remove('specialty'));
            this.classList.add('specialty');
            
            currentSpecialty = fieldId;
            specialtyFieldX = (fieldId * 2) - 1;

            if (MODE === 'create') {
                // hidden input 업데이트 (폼 제출용)
                const hiddenField = document.getElementById('specialty_field');
                if (hiddenField) hiddenField.value = fieldId;
            }
            // sheet 모드에서 AJAX 저장은 더 이상 불필요
        });
    });
}

/**
 * [기능 4] 특기 습득/해제
 */
function handleCellClick(e) {
    if (MODE === 'create') {
        handleCreateClick(e);
    } else if (MODE === 'sheet') {
        handleSheetClick(e);
    }
}

// ===== CREATE 모드: 특기 선택/해제 (5개 제한, 서버 저장 없음) =====
function handleCreateClick(e) {
    const cell = e.currentTarget;
    const skillId = parseInt(cell.dataset.id);

    if (cell.classList.contains('owned')) {
        // 해제
        cell.classList.remove('owned');
        ownedSkillIds = ownedSkillIds.filter(id => id !== skillId);
    } else {
        // 선택
        if (ownedSkillIds.length >= MAX_SKILLS) {
            showCreateError(`최대 ${MAX_SKILLS}개의 특기만 선택 가능`);
            return;
        }
        cell.classList.add('owned');
        ownedSkillIds.push(skillId);
    }

    updateSkillCount();
    // hidden input 업데이트 (폼 제출용)
    document.getElementById('skills').value = JSON.stringify(ownedSkillIds);
}

// ===== SHEET 모드: 판정만 (편집 기능 제거) =====
function handleSheetClick(e) {
    // 시트에서는 클릭 = 판정 (잠금 ON 상태에서만)
    const lockSwitch = document.getElementById('lock-switch');
    if (!lockSwitch || !lockSwitch.checked) return;

    // 판정 로직은 handleMouseDown/Up에서 처리하므로
    // 여기서는 아무것도 안 해도 됨
    // (혹시 단순 클릭 판정을 원하면 여기에 추가)
}

function updateSkillCount() {
    const countEl = document.getElementById('skill-count');
    if (countEl) {
        countEl.textContent = ownedSkillIds.length;
    }
}

function showCreateError(message) {
    const errorEl = document.getElementById('error-message');
    if (!errorEl) return;
    errorEl.textContent = message;
    errorEl.style.display = 'block';
    setTimeout(() => { errorEl.style.display = 'none'; }, 3000);
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