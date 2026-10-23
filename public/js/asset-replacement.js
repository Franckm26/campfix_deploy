document.addEventListener('click', async function (event) {
    const button = event.target.closest('[data-record-asset-replacement]');
    if (!button) return;
    event.preventDefault();
    const content = document.createElement('div');
    content.innerHTML = '<p data-asset-issue class="fw-semibold"></p><p class="small">This records a complete asset replacement. The previous asset history stays available, and the new asset starts a fresh recurrence count.</p><label for="replacementDetails" class="form-label">New asset details (model, serial number or asset tag)</label><input id="replacementDetails" class="form-control" maxlength="255"><label for="replacementNotes" class="form-label mt-3">Replacement notes</label><textarea id="replacementNotes" class="form-control" maxlength="1000" rows="3"></textarea><label class="mt-3"><input type="checkbox" id="replacementConfirmed"> A complete asset was replaced, not just a repaired part.</label>';
    content.querySelector('[data-asset-issue]').textContent = button.dataset.issue + ' — ' + button.dataset.location;
    const target = Array.from(document.querySelectorAll('.modal.show')).pop() || document.body;
    const validation = message => {
        Swal.showValidationMessage('Please check the replacement details.');
        Swal.getValidationMessage().textContent = message;
        return false;
    };
    const result = await Swal.fire({
        title: 'Record asset replacement', html: content, target,
        showCancelButton: true, confirmButtonText: 'Record replacement',
        confirmButtonColor: '#0d6efd', showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: async () => {
            const details = content.querySelector('#replacementDetails').value.trim();
            const notes = content.querySelector('#replacementNotes').value.trim();
            const confirmed = content.querySelector('#replacementConfirmed').checked;
            if (!details || !notes) return validation('Enter the new asset details and replacement notes.');
            if (!confirmed) return validation('Confirm that a complete asset was replaced.');
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ replacement_details: details, replacement_notes: notes,
                        confirmed_replacement: true, operation_id: button.dataset.operationId })
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || !data.success) return validation(Object.values(data.errors || {}).flat().join('\n') || data.message || data.error || 'Unable to record the replacement. Please try again.');
                return data;
            } catch (error) { return validation('Unable to connect. Check your connection and try again.'); }
        }
    });
    if (result.isConfirmed) {
        await Swal.fire({ icon: 'success', title: 'Replacement recorded', text: result.value.message, target });
        window.location.reload();
    }
});
