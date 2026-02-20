/**
 * create_char.js
 * 캐릭터 생성 폼 로직
 */
const OUTSIDER_ARCHETYPE_ID = 5; // 이단자 경력 ID

document.addEventListener('DOMContentLoaded', () => {
    initFormEvents();
    initGenderCustomInput();
    initMemoFloating();
    initAncestryPeerageToggle();
});

/* 경력 선택에 따른 숨겨져 있던 종족/작위 활성화 및 초기화 */
function initAncestryPeerageToggle() {
    let outsiderOptionRow = document.getElementById('ancestry-peerage-column');
    const archetypeSelect = document.getElementById('archetype_id');
    let ancestrySelect = document.getElementById('ancestry_id');
    const profileRight = document.querySelector('.profile-right');
    const ancestryPeerageRowHTML = outsiderOptionRow ? outsiderOptionRow.outerHTML : '';

    // [1] 종족 선택 여부에 따라 작위 필드 제어
    function updatePeerageVisibility() {
        // 이단자가 아니면 무시 (상위 함수에서 처리)
        const ancestryRow = document.getElementById('ancestry-peerage-column');
        if (!ancestryRow) return;

        // peerage-section이 이미 있는지 확인
        let peerageSection = ancestryRow.parentElement.querySelector('#peerage-section');
        const hasAncestry = ancestrySelect.value && ancestrySelect.value !== '' && ancestrySelect.value !== '1'; // 1번은 이종족 사용 안 함

        if (hasAncestry) {
            // peerage-section이 없으면 생성해서 ancestryRow 뒤에 삽입
            if (!peerageSection) {
                const peerages = window.PEERAGES || [];
                let peerageOptions = '<option value="">선택하세요</option>';
                peerages.forEach(p => {
                    peerageOptions += `<option value="${p.id}">${p.peerage_name}</option>`;
                });
                const peerageHTML = `
                <div class="compact-field" id="peerage-section">
                    <label>작위 (이종족 전용)<span class="required">*</span></label>
                    <select name="peerage_id" id="peerage_id">
                        ${peerageOptions}
                    </select>
                    <p class="info-text">종족의 위계를 나타냅니다.</p>
                </div>`;
                ancestryRow.insertAdjacentHTML('afterend', peerageHTML);
                peerageSection = ancestryRow.parentElement.querySelector('#peerage-section');
            }
            // 활성화 및 기본값 설정
            const peerageSelect = peerageSection.querySelector('#peerage_id');
            peerageSection.style.display = 'flex';
            peerageSection.style.opacity = '1';
            peerageSelect.disabled = false;
            // 작위 기본값 자동 설정 (남작)
            if (!peerageSelect.value && peerageSelect.options.length > 1) {
                peerageSelect.value = peerageSelect.options[1].value;
            }
        } else {
            // peerage-section이 있으면 제거
            if (peerageSection) peerageSection.remove();
        }
    }

    // [2] 경력(이단자) 선택에 따라 전체 행 제어
    function updateAncestryPeerage() {
        const isOutsider = archetypeSelect.value == String(OUTSIDER_ARCHETYPE_ID);
        let ancestryRow = document.getElementById('ancestry-peerage-column');

        if (isOutsider) {
            // DOM에 없으면 삽입
            if (!ancestryRow) {
                // 백스토리 textarea-group 앞의 compact-row 내부에 삽입
                let backstoryGroup = profileRight.querySelector('.compact-field.textarea-group');
                if (backstoryGroup) {
                    let compactRow = backstoryGroup.closest('.compact-column').querySelector('.compact-row');
                    if (compactRow) {
                        compactRow.insertAdjacentHTML('afterbegin', ancestryPeerageRowHTML);
                    }
                }
                // 새로 삽입된 요소에 대해 변수/이벤트 재등록
                outsiderOptionRow = document.getElementById('ancestry-peerage-column');
                ancestrySelect = document.getElementById('ancestry_id');
                ancestrySelect.addEventListener('change', updatePeerageVisibility);
            }
            ancestrySelect.disabled = false;
            updatePeerageVisibility();
        } else {
            // DOM에 있으면 제거
            if (ancestryRow) {
                // peerage-section도 함께 제거
                let peerageSection = ancestryRow.parentElement.querySelector('#peerage-section');
                if (peerageSection) peerageSection.remove();
                ancestryRow.remove();
            }
        }
    }

    // 이벤트 리스너 등록
    archetypeSelect.addEventListener('change', updateAncestryPeerage);
    if (ancestrySelect) ancestrySelect.addEventListener('change', updatePeerageVisibility);
    updateAncestryPeerage();
}

/* 폼 이벤트 초기화 */
function initFormEvents() {
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
    
    if (!currentSpecialty) {
        showError('전문 분야를 선택해주세요. (상단 카테고리 클릭)');
        return;
    }
    
    if (ownedSkillIds.length !== SHEET_CONFIG.maxSkills) {
        showError(`특기를 정확히 ${SHEET_CONFIG.maxSkills}개 선택해주세요.`);
        return;
    }

    // 전문 분야 내 2개 이상 검증 (신규)
    const specialtySkillCount = ownedSkillIds.filter(id => {
        return Math.floor(id / 100) === currentSpecialty;
    }).length;
    if (specialtySkillCount < 2) {
        showError('전문 분야 내에서 2개 이상의 특기를 선택해야 합니다.');
        return;
    }
    
    // 폼 데이터 설정
    document.getElementById('specialty_field').value = currentSpecialty;
    document.getElementById('skills').value = JSON.stringify(ownedSkillIds);
    
    // 폼 제출
    document.getElementById('create-char-form').submit();
}