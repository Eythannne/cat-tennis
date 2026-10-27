// Recadrage d'image avant envoi (utilisé par articles.php et entraineurs.php).
// L'input #image_picker porte la forme du cadre : data-ratio, data-width, data-height.
// L'image recadrée est mise en base64 dans #image_cropped, envoyé avec le formulaire.
(function() {
    const picker      = document.getElementById('image_picker');
    if (!picker) return;
    const overlay     = document.getElementById('crop_overlay');
    const cropImg     = document.getElementById('crop_img');
    const btnCancel   = document.getElementById('crop_cancel');
    const btnConfirm  = document.getElementById('crop_confirm');
    const hiddenInput = document.getElementById('image_cropped');
    const previewWrap = document.getElementById('crop_preview_wrap');
    const previewThumb= document.getElementById('crop_preview_thumb');
    const btnRedo     = document.getElementById('crop_redo');

    const ratio  = parseFloat(picker.dataset.ratio || (16 / 9));
    const width  = parseInt(picker.dataset.width || 800, 10);
    const height = parseInt(picker.dataset.height || 450, 10);

    let cropper = null;
    let originalFile = null;

    function openCropper(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            cropImg.src = e.target.result;
            overlay.classList.add('active');
            // Init cropper après que l'image soit chargée
            cropImg.onload = () => {
                if (cropper) { cropper.destroy(); cropper = null; }
                cropper = new Cropper(cropImg, {
                    aspectRatio: ratio,
                    viewMode: 1,
                    autoCropArea: 0.9,
                    movable: true,
                    zoomable: true,
                    scalable: false,
                    rotatable: false,
                });
            };
        };
        reader.readAsDataURL(file);
    }

    picker.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        originalFile = file;
        openCropper(file);
    });

    btnCancel.addEventListener('click', () => {
        overlay.classList.remove('active');
        if (cropper) { cropper.destroy(); cropper = null; }
        picker.value = '';
        originalFile = null;
    });

    btnConfirm.addEventListener('click', () => {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({ width, height });
        const base64 = canvas.toDataURL('image/jpeg', 0.88);

        hiddenInput.value = base64;
        previewThumb.src  = base64;
        previewWrap.hidden = false;

        overlay.classList.remove('active');
        cropper.destroy(); cropper = null;
    });

    btnRedo.addEventListener('click', () => {
        if (originalFile) openCropper(originalFile);
        else picker.click();
    });
})();
