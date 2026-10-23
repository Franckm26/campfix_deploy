document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('userImportForm');
    if (!form) return;

    let submitting = false;
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (submitting) return;
        submitting = true;
        const payload = new FormData(form);
        const role = payload.get('default_role');
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('importModal')).hide();
        Swal.fire({
            title: 'Importing users...',
            text: 'Please wait while your file is checked.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: payload
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                const fallback = response.status === 413
                    ? 'The upload is too large. Please choose a file smaller than 2 MB.'
                    : response.status === 419 || response.status === 401
                        ? 'Your session has expired. Refresh the page and sign in again.'
                        : 'The file could not be imported. Please choose a valid user CSV, TXT or XLSX file and try again.';
                throw new Error(Object.values(data.errors || {}).flat().join('\n') || data.error || data.message || fallback);
            }
            await Swal.fire({ icon: 'success', title: 'Import complete', text: data.message, confirmButtonColor: '#0d6efd' });
            window.location.assign(data.redirect);
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Unable to import users',
                text: error instanceof TypeError ? 'Unable to connect. Check your connection and try again.' : error.message,
                confirmButtonText: 'Choose another file',
                confirmButtonColor: '#0d6efd'
            });
            if (['student', 'faculty', 'staff'].includes(role)) selectImportRole(role);
            bootstrap.Modal.getOrCreateInstance(document.getElementById('importModal')).show();
        } finally {
            submitting = false;
            button.disabled = false;
        }
    });
});
