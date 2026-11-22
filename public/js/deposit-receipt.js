(() => {
    const copyStatus = document.querySelector('[data-deposit-copy-status]');
    for (const button of document.querySelectorAll('[data-deposit-copy]')) {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.depositCopy);
                copyStatus.textContent = button.dataset.copyLabel + ' copied.';
            } catch {
                copyStatus.textContent = 'Select the value and copy it manually.';
            }
            copyStatus.hidden = false;
        });
    }

    const fileInput = document.getElementById('receipt_image');
    const preview = document.querySelector('[data-deposit-preview]');
    if (!fileInput || !preview) return;
    const image = preview.querySelector('img');
    let previewUrl;

    function updatePreview() {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        image.removeAttribute('src');

        const file = fileInput.files[0];
        preview.hidden = !file;
        if (file && file.type.startsWith('image/')) {
            previewUrl = URL.createObjectURL(file);
            image.src = previewUrl;
        }
        image.hidden = !previewUrl;
    }

    fileInput.addEventListener('change', updatePreview);
    preview.querySelector('[data-deposit-clear]').addEventListener('click', () => {
        fileInput.value = '';
        updatePreview();
        fileInput.focus();
    });

    updatePreview();
})();
