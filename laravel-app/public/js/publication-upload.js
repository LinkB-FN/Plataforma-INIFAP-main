(() => {
    const form = document.getElementById('publication-upload-form');
    if (!form) return;
    const source = form.querySelector('#origen');
    const file = form.querySelector('#file');
    const cover = form.querySelector('#portada');
    const preview = form.querySelector('#cover-preview');
    const originalPreview = preview.getAttribute('src');
    const limits = JSON.parse(form.dataset.limits);
    const button = form.querySelector('#upload-submit');
    const buttonLabel = button.textContent;
    const status = form.querySelector('#upload-status');
    let previewUrl;
    let submitting = false;

    function updateSource() {
        form.querySelectorAll('[data-source]').forEach(section => {
            const active = section.dataset.source === source.value;
            section.hidden = !active;
            section.querySelectorAll('input').forEach(input => {
                input.disabled = !active;
                input.required = active;
            });
        });
    }
    source.addEventListener('change', updateSource);
    updateSource();

    file.addEventListener('change', () => {
        file.setCustomValidity('');
        const selected = file.files[0];
        const feedback = form.querySelector('#file-feedback');
        feedback.textContent = '';
        if (!selected) return;
        const extension = selected.name.split('.').pop().toLowerCase();
        if (!(extension in limits)) {
            file.setCustomValidity('Selecciona un PDF, MP3, video (MP4, MOV o WebM) o imagen (JPG, PNG o WebP).');
        } else if (selected.size > limits[extension] * 1024) {
            file.setCustomValidity('Este archivo supera el límite de ' + limits[extension] / 1024 + ' MB.');
        }
        feedback.textContent = selected.name + ' · ' + (selected.size / 1024 / 1024).toFixed(1) + ' MB';
        file.reportValidity();
    });

    cover.addEventListener('change', () => {
        cover.setCustomValidity('');
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        preview.hidden = !originalPreview;
        if (originalPreview) preview.src = originalPreview;
        else preview.removeAttribute('src');
        const selected = cover.files[0];
        if (!selected) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(selected.type) || selected.size > 5 * 1024 * 1024) {
            cover.setCustomValidity('Selecciona una imagen JPG, PNG o WebP de hasta 5 MB.');
            cover.reportValidity();
            return;
        }
        previewUrl = URL.createObjectURL(selected);
        preview.src = previewUrl;
        preview.hidden = false;
    });

    form.addEventListener('submit', event => {
        if (submitting) {
            event.preventDefault();
            return;
        }
        submitting = true;
        button.disabled = true;
        button.textContent = source.value === 'archivo' ? 'Subiendo…' : 'Guardando…';
        status.textContent = 'Espera a que termine el envío. Los archivos grandes pueden tardar unos momentos.';
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', () => {
        submitting = false;
        button.disabled = false;
        button.textContent = buttonLabel;
        status.textContent = '';
        form.removeAttribute('aria-busy');
        updateSource();
    });
})();
