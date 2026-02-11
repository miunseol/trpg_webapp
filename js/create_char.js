/**
 * create_char.js
 * 캐릭터 생성 폼 로직
 */

let selectedSkills = [];
let selectedSpecialty = null;
const MAX_SKILLS = 6;
const OUTSIDER_ARCHETYPE_ID = 5; // 이단자 경력 ID

document.addEventListener('DOMContentLoaded', () => {
    initFormEvents();
    initSkillSelection();
    initGenderCustomInput();
    initMemoFloating();
    initAncestryPeerageToggle();
});

/* 경력 선택에 따른 숨겨져 있던 종족/작위 활성화 및 초기화 */
function initAncestryPeerageToggle() {
    const outsiderOptionRow = document.getElementById('ancestry-peerage-row');
    const archetypeSelect = document.getElementById('archetype_id');
    const ancestrySelect = document.getElementById('ancestry_id');
    const peerageSelect = document.getElementById('peerage_id');
    const peerageSection = document.getElementById('peerage-section');

    // [1] 종족 선택 여부에 따라 작위 필드 제어
    function updatePeerageVisibility() {
        // 이단자가 아니면 무시 (상위 함수에서 처리)
        if (archetypeSelect.value != '5') return;

        const hasAncestry = ancestrySelect.value != '1';   //1번은 이종족 사용 안 함
        
        if (hasAncestry) {
            // 종족 선택됨 -> 작위 표시 & 활성화
            peerageSection.style.display = 'block'; 
            peerageSection.style.opacity = '1';
            peerageSelect.disabled = false;
        } else {
            // 종족 선택 안됨 -> 작위 숨김 & 비활성화
            peerageSection.style.display = 'none';
            peerageSection.style.opacity = '0';
            peerageSelect.disabled = true;
            peerageSelect.value = ''; // 값 초기화
        }
    }

    // [2] 경력(이단자) 선택에 따라 전체 행 제어
    function updateAncestryPeerage() {
        const isOutsider = archetypeSelect.value == '5';
        
        // 이단자 옵션 행 전체 표시/숨김
        outsiderOptionRow.style.opacity = isOutsider ? 1 : 0.5;
        outsiderOptionRow.style.display = isOutsider ? 'grid' : 'none';
        ancestrySelect.disabled = !isOutsider;

        if (isOutsider) {
            // 이단자라면 -> 종족 선택 상태 체크해서 작위 표시 결정
            updatePeerageVisibility();
        } else {
            // 이단자 아니면 -> 싹 다 초기화 및 숨김
            ancestrySelect.value = '';
            peerageSelect.value = '';
            peerageSelect.disabled = true;
            peerageSection.style.display = 'none'; 
        }
    }

    // 이벤트 리스너 등록
    archetypeSelect.addEventListener('change', updateAncestryPeerage);
    ancestrySelect.addEventListener('change', updatePeerageVisibility);

    updateAncestryPeerage();
}

/* 폼 이벤트 초기화 */
function initFormEvents() {
    // 성별 선택 시 직접입력 표시
    document.getElementById('gender').addEventListener('change', function() {
        const customInput = document.getElementById('gender-custom-input');
        if (this.value === 'custom') {
            customInput.classList.add('show')
        } else {
            customInput.classList.remove('show')
            document.getElementById('gender_custom').value = '' ;
        }
    });
}

/* 특기 선택 초기화 */
function initSkillSelection() {
    // 전문 분야 선택
    const categories = document.querySelectorAll('.cat');
    categories.forEach(cat => {
        cat.addEventListener('click', function() {
            const fieldId = parseInt(this.dataset.field);
            
            document.querySelectorAll('.cat').forEach(c => c.classList.remove('specialty'));
            this.classList.add('specialty');
            
            selectedSpecialty = fieldId;
        });
    });

    // 특기 선택
    const skillCells = document.querySelectorAll('.skill-cell');
    skillCells.forEach(cell => {
        cell.addEventListener('click', function() {
            const skillId = parseInt(this.dataset.id);
            
            if (this.classList.contains('owned')) {
                this.classList.remove('owned');
                selectedSkills = selectedSkills.filter(id => id !== skillId);
            } else {
                if (selectedSkills.length >= MAX_SKILLS) {
                    showError(`최대 ${MAX_SKILLS}개의 특기만 선택할 수 있습니다.`);
                    return;
                }
                this.classList.add('owned');
                selectedSkills.push(skillId);
            }
            
            updateSkillCount();
        });
    });
}

/* 성별 직접입력 토글 초기화 */
function initGenderCustomInput() {
    const genderSelect = document.getElementById('gender');
    const genderCustom = document.getElementById('gender_custom');
    // select와 input을 감싸는 부모 요소(.gender-group)를 찾음
    const genderGroup = genderSelect.parentElement; 

    function updateGenderCustom() {
        if (genderSelect.value === 'custom') {
            // 클래스 추가 -> CSS 애니메이션 발동 (드롭다운 줄어듦, 인풋 나타남)
            genderGroup.classList.add('custom-mode');
            genderCustom.focus(); // 센스 있게 포커스도 이동
        } else {
            // 클래스 제거 -> 원래대로 복귀
            genderGroup.classList.remove('custom-mode');
            genderCustom.value = ''; // 값 초기화
        }
    }

    genderSelect.addEventListener('change', updateGenderCustom);
    
    // 초기 로드 시 상태 반영 (수정 페이지 등을 위해)
    updateGenderCustom();
}

/* 특기 카운트 업데이트 */
function updateSkillCount() {
    document.getElementById('skill-count').textContent = selectedSkills.length;
}

/* 백스토리 자동 높이 조절 & 플로팅 */
function initMemoFloating() {
    const backstory = document.getElementById('backstory');
    
    // 내용에 맞춰 높이 자동 조절 함수
    const autoResize = () => {
        backstory.style.height = 'auto'; // 일단 높이 초기화
        backstory.style.height = backstory.scrollHeight + 'px'; // 내용만큼 늘리기
    };

    backstory.addEventListener('focus', () => {
        // 포커스 되면 일단 내용을 다 보여줄 만큼 늘림
        autoResize();
        // 입력할 때마다 늘어남
        backstory.addEventListener('input', autoResize);
    });

    backstory.addEventListener('blur', () => {
        // 포커스 잃으면 이벤트 제거하고 원래 높이(CSS설정)로 복귀
        backstory.removeEventListener('input', autoResize);
        backstory.style.height = ''; // inline style 제거 -> CSS height(100%) 적용
    });
}

/* 에러 메시지 표시 */
function showError(message) {
    const errorEl = document.getElementById('error-message');
    errorEl.textContent = message;
    errorEl.style.display = 'block';
    setTimeout(() => {
        errorEl.style.display = 'none';
    }, 3000);
}

/* 폼 유효성 검사 및 제출 */
function validateAndSubmit() {
    const charName = document.getElementById('char_name').value.trim();
    const magicName = document.getElementById('magic_name').value.trim();
    const gender = document.getElementById('gender').value;
    const archetypeId = parseInt(document.getElementById('archetype_id').value);
    
    if (!charName) {
        showError('캐릭터 이름을 입력해주세요.');
        return;
    }
    
    if (!magicName) {
        showError('마법명을 입력해주세요.');
        return;
    }

    if (!gender) {
        showError('성별을 선택해주세요.');
        return;
    }

    if (gender === 'custom') {
        const customGender = document.getElementById('gender_custom').value.trim();
        if (!customGender) {
            showError('성별을 직접 입력해주세요.');
            return;
        }
    }

    if (!archetypeId) {
        showError('경력을 선택해주세요.');
        return;
    }

    // 이단자 + 이종족 선택 시에만 작위 필수
    if (archetypeId === OUTSIDER_ARCHETYPE_ID) {
        const ancestryId = document.getElementById('ancestry_id').value;
        
        // 종족을 선택했으면 작위도 필수
        if (ancestryId) {
            const peerageId = document.getElementById('peerage_id').value;
            if (!peerageId) {
                showError('이종족을 선택한 경우 작위 선택이 필수입니다.');
                return;
            }
        }
    }
    
    if (!selectedStrong) {
        showError('전문 분야를 선택해주세요. (상단 카테고리 클릭)');
        return;
    }
    
    if (selectedSkills.length !== MAX_SKILLS) {
        showError(`초기 특기를 정확히 ${MAX_SKILLS}개 선택해주세요. (현재: ${selectedSkills.length}개)`);
        return;
    }
    
    // 폼 데이터 설정
    document.getElementById('strong_field').value = selectedSpecialty;
    document.getElementById('skills').value = JSON.stringify(selectedSkills);
    
    // 폼 제출
    document.getElementById('create-char-form').submit();
}