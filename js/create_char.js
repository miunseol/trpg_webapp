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
});

/**
 * 폼 이벤트 초기화
 */
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

    // 경력 선택 시 조건부 필드 토글
    document.getElementById('archetype_id').addEventListener('change', function() {
        
        // 이단자 선택 시 종족 필드 표시 (선택사항)
        const archetypeId = parseInt(this.value);
        const ancestrySection = document.getElementById('ancestry-section');
        
        if (archetypeId === OUTSIDER_ARCHETYPE_ID) {
            ancestrySection.classList.add('show');
        } else {
            ancestrySection.classList.remove('show');
            document.getElementById('ancestry_id').value = '';
            // 종족 초기화 시 작위도 숨김
            document.getElementById('peerage-section').classList.remove('show');
            document.getElementById('peerage_id').removeAttribute('required');
            document.getElementById('peerage_id').value = '';
        }
    });

    // 종족 선택 시 작위 필드 토글
    document.getElementById('ancestry_id').addEventListener('change', function() {
        
        // 종족 선택 시에만 작위 필드 표시 (필수)
        const peerageSection = document.getElementById('peerage-section');
        if (this.value) {
            peerageSection.classList.add('show');
            document.getElementById('peerage_id').setAttribute('required', 'required');
        } else {
            peerageSection.classList.remove('show');
            document.getElementById('peerage_id').removeAttribute('required');
            document.getElementById('peerage_id').value = '';
        }
    });
}

/**
 * 특기 선택 초기화
 */
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

function initGenderCustomInput() {
    const genderSelect = document.getElementById('gender');
    const genderCustom = document.getElementById('gender_custom');
    function updateGenderCustom() {
        if (genderSelect.value === 'custom') {
            genderCustom.style.opacity = 1;
            genderCustom.style.pointerEvents = 'auto';
        } else {
            genderCustom.style.opacity = 0;
            genderCustom.style.pointerEvents = 'none';
        }
    }
    genderSelect.addEventListener('change', updateGenderCustom);
    updateGenderCustom();
};

/**
 * 특기 카운트 업데이트
 */
function updateSkillCount() {
    document.getElementById('skill-count').textContent = selectedSkills.length;
}

/**
 * 에러 메시지 표시
 */
function showError(message) {
    const errorEl = document.getElementById('error-message');
    errorEl.textContent = message;
    errorEl.style.display = 'block';
    setTimeout(() => {
        errorEl.style.display = 'none';
    }, 3000);
}

/**
 * 폼 유효성 검사 및 제출
 */
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