(function () {
    const feedback = {
        validate(file) {
            if (!file) return '';
            if (!/\.(jpe?g|png|gif)$/i.test(file.name)
                || (file.type && !['image/jpeg', 'image/png', 'image/gif'].includes(file.type))) {
                return 'Please choose a JPEG, PNG or GIF image. Other file types are not supported.';
            }
            return file.size > 2 * 1024 * 1024 ? 'The image is too large. Please choose an image no larger than 2 MB.' : '';
        },
        show(message, title = 'Unable to upload image') {
            const modal = Array.from(document.querySelectorAll('.modal.show')).pop();
            return Swal.fire({
                icon: 'error', title, text: message,
                target: modal || document.body,
                confirmButtonText: 'OK', confirmButtonColor: '#0d6efd'
            });
        },
        async readResponse(response, fallback = 'The image could not be uploaded. Please try again.') {
            const body = await response.json().catch(() => null);
            const data = body && typeof body === 'object' ? body : {};
            if (!response.ok || data.success !== true) {
                const message = Object.values(data.errors || {}).flat().join('\n')
                    || (response.status === 413 ? 'The image is too large. Please choose an image smaller than 2 MB.' : '')
                    || data.error || data.message || fallback;
                throw new Error(message);
            }
            return data;
        }
    };
    window.ImageUploadFeedback = feedback;

    // Delegation also covers concern edit forms loaded into modals later.
    document.addEventListener('change', function (event) {
        const input = event.target;
        if (!input.matches('input[type="file"][name="image"]')) return;
        const error = feedback.validate(input.files[0]);
        if (error) {
            input.value = '';
            feedback.show(error, 'Invalid concern image');
        }
    }, true);
    document.addEventListener('submit', function (event) {
        const input = event.target.querySelector('input[type="file"][name="image"]');
        const error = input && feedback.validate(input.files[0]);
        if (error) {
            event.preventDefault();
            event.stopImmediatePropagation();
            feedback.show(error, 'Invalid concern image');
            return;
        }
        const form = event.target;
        if (!form.matches('[data-concern-upload-form]')) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        if (form.dataset.uploading === 'true') return;
        form.dataset.uploading = 'true';
        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
            .then(response => feedback.readResponse(response, 'The concern could not be saved. Please try again.'))
            .then(() => window.location.assign(form.dataset.successUrl))
            .catch(error => feedback.show(error.message, 'Unable to save concern'))
            .finally(() => { form.dataset.uploading = 'false'; });
    }, true);
})();
