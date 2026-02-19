/**
 * 이미지 업로드 & 크롭 시스템
 */

let currentCropper = null;
let currentTargetArea = null;
let currentHiddenInput = null;

document.addEventListener('DOMContentLoaded', () => {
    initImageUpload();
});

function initImageUpload() {
    const uploadAreas = document.querySelectorAll('.image-upload-area');
    
    uploadAreas.forEach(area => {
        const placeholder = area.querySelector('.upload-placeholder');
        const preview = area.querySelector('.preview-image');
        const fileInput = area.querySelector('.file-input');
        const targetInputId = area.dataset.target;
        const hiddenInput = document.getElementById(targetInputId);
        
        // 클릭하여 파일 선택
        area.addEventListener('click', (e) => {
            if (e.target.closest('.remove-btn')) return;
            fileInput.click();
        });
        
        // 파일 선택 시
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                openCropModal(file, area, preview, placeholder, hiddenInput);
            }
        });
        
        // 드래그 오버
        area.addEventListener('dragover', (e) => {
            e.preventDefault();
            area.classList.add('drag-over');
        });
        
        // 드래그 떠남
        area.addEventListener('dragleave', (e) => {
            e.preventDefault();
            area.classList.remove('drag-over');
        });
        
        // 드롭
        area.addEventListener('drop', (e) => {
            e.preventDefault();
            area.classList.remove('drag-over');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                openCropModal(file, area, preview, placeholder, hiddenInput);
            } else {
                alert('이미지 파일만 업로드 가능합니다.');
            }
        });
    });
}

/**
 * 크롭 모달 열기
 */
function openCropModal(file, area, preview, placeholder, hiddenInput) {
    // 파일 크기 체크 (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('파일 크기는 5MB 이하만 가능합니다.');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        const modal = document.getElementById('crop-modal');
        const cropImage = document.getElementById('crop-image');
        
        cropImage.src = e.target.result;
        modal.style.display = 'flex';
        
        // Cropper 초기화
        if (currentCropper) {
            currentCropper.destroy();
        }

        const targetId = area.dataset.target;
        const aspectRatio = (targetId === 'sovereignty_image') ? 16 / 9 : 1; // 주권 이미지는 16:9, 나머지는 1:1
        
        currentCropper = new Cropper(cropImage, {
            aspectRatio: aspectRatio,
            viewMode: 2,
            autoCropArea: 1,
            responsive: true,
            background: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
        
        currentTargetArea = { area, preview, placeholder };
        currentHiddenInput = hiddenInput;
    };
    
    reader.readAsDataURL(file);
}

/**
 * 크롭 모달 닫기
 */
function closeCropModal() {
    const modal = document.getElementById('crop-modal');
    modal.style.display = 'none';
    
    if (currentCropper) {
        currentCropper.destroy();
        currentCropper = null;
    }
    
    currentTargetArea = null;
    currentHiddenInput = null;
}

/**
 * 크롭 확정
 */
function confirmCrop() {
    if (!currentCropper || !currentTargetArea) return;
    
    // 크롭된 이미지를 Canvas로 변환
    const isSovereignty = currentTargetArea?.area?.dataset?.target === 'sovereignty_image';
    const canvas = currentCropper.getCroppedCanvas({
        width: isSovereignty ? 960 : 512,  // 주권 이미지는 1024, 나머지는 512
        height: isSovereignty ? 540 : 512,  // 주권 이미지는 16:9, 나머지는 1:1
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    // Canvas를 Blob으로 변환
    canvas.toBlob((blob) => {
        // 미리보기 표시
        const { area, preview, placeholder } = currentTargetArea;
        preview.src = canvas.toDataURL();
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        
        // 삭제 버튼 추가
        if (!area.querySelector('.remove-btn')) {
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-btn';
            removeBtn.innerHTML = '✕';
            removeBtn.onclick = (e) => {
                e.stopPropagation();
                removeImage(area, placeholder, preview, currentHiddenInput);
            };
            area.appendChild(removeBtn);
        }
        
        // 서버에 업로드
        uploadToServer(blob, currentHiddenInput);
        
        // 모달 닫기
        closeCropModal();
    }, 'image/jpeg', 0.9);
}

/**
 * 서버에 이미지 업로드
 */
function uploadToServer(blob, hiddenInput) {
    const formData = new FormData();
    formData.append('image', blob, 'cropped.jpg');
    
    console.log('업로드 중...');
    
    fetch('upload_image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hiddenInput.value = data.url;
            console.log('업로드 성공:', data.url);
        } else {
            alert('업로드 실패: ' + data.message);
        }
    })
    .catch(error => {
        console.error('업로드 에러:', error);
        alert('업로드 중 오류가 발생했습니다.');
    });
}

/**
 * 이미지 제거
 */
function removeImage(area, placeholder, preview, hiddenInput) {
    preview.style.display = 'none';
    preview.src = '';
    placeholder.style.display = 'flex';
    hiddenInput.value = '';
    
    const removeBtn = area.querySelector('.remove-btn');
    if (removeBtn) {
        removeBtn.remove();
    }
}