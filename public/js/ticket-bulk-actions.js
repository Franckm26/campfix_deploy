(function () {
    const config = window.ticketBulkConfig;
    const groups = {
        active: { pane: 'active-concerns', master: ['selectAllActive', 'selectAllReports'] },
        resolved: { pane: 'resolved-concerns', master: ['selectAllResolved'] },
        archive: { pane: 'archived-concerns', master: ['selectAllArchive', 'selectAllArchived'] },
        deleted: { pane: 'deleted-concerns', master: ['selectAllDeleted', 'deletedReportsSelectAll'] }
    };
    const selections = Object.fromEntries(Object.keys(groups).map(group => [group, new Set()]));
    const boxes = group => Array.from(document.querySelectorAll('.' + group + '-checkbox'));
    function update(group) {
        const selected = selections[group];
        const items = boxes(group);
        const ids = new Set(items.map(box => box.value));
        items.forEach(box => { box.checked = selected.has(box.value); });
        const bar = document.getElementById(group + 'BulkActions');
        const count = document.getElementById(group + 'SelectedCount');
        if (bar) bar.style.display = selected.size ? 'block' : 'none';
        if (count) count.textContent = selected.size + ' selected';
        groups[group].master.forEach(id => {
            const master = document.getElementById(id);
            if (master) {
                master.checked = ids.size > 0 && selected.size === ids.size;
                master.indeterminate = selected.size > 0 && selected.size < ids.size;
            }
        });
    }
    function toggle(group, master) {
        const checked = master ? master.checked : groups[group].master.map(id => document.getElementById(id)).find(Boolean)?.checked;
        selections[group].clear();
        if (checked) boxes(group).forEach(box => selections[group].add(box.value));
        update(group);
    }
    document.addEventListener('change', event => {
        for (const group of Object.keys(groups)) {
            if (event.target.classList.contains(group + '-checkbox')) {
                if (event.target.checked) selections[group].add(event.target.value);
                else selections[group].delete(event.target.value);
                update(group);
                break;
            }
        }
    });
    document.addEventListener('submit', async event => {
        if (!event.target.querySelector('[data-ticket-delete-confirm]')) return;
        event.preventDefault();
        const result = await Swal.fire({ icon: 'warning', title: 'Permanently delete report?', text: 'This cannot be undone.', showCancelButton: true, confirmButtonText: 'Delete permanently', confirmButtonColor: '#dc3545' });
        if (result.isConfirmed) event.target.submit();
    });
    let busy = false;
    async function run(group, action) {
        if (busy) return;
        const ids = Array.from(selections[group]);
        if (!ids.length) return Swal.fire({ icon: 'info', title: 'No tickets selected', text: 'Select at least one ticket first.' });
        const verbs = { archive: 'Archive', restore: 'Restore', 'soft-delete': 'Delete', 'restore-deleted': 'Restore', 'permanent-delete': 'Permanently delete' };
        const verb = verbs[action];
        const permanent = action === 'permanent-delete';
        busy = true;
        try {
            const confirmation = await Swal.fire({
                icon: 'warning', title: verb + ' selected tickets?',
                text: verb + ' ' + ids.length + ' selected ' + config.resource + '?' + (permanent ? ' This cannot be undone.' : ''),
                showCancelButton: true, confirmButtonText: verb, cancelButtonText: 'Cancel',
                confirmButtonColor: action.includes('delete') ? '#dc3545' : '#0d6efd'
            });
            if (!confirmation.isConfirmed) return;
            if (permanent && !(await Swal.fire({ icon: 'warning', title: 'Confirm permanent deletion', text: 'These deleted tickets will be removed permanently.', showCancelButton: true, confirmButtonText: 'Delete permanently', confirmButtonColor: '#dc3545' })).isConfirmed) return;
            Swal.fire({ title: 'Updating tickets...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });
            const response = await fetch(config.urls[action], {
                method: 'POST', headers: { 'X-CSRF-TOKEN': config.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ ids })
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.success !== true) throw new Error(Object.values(data.errors || {}).flat().join('\n') || data.message || data.error || 'The selected tickets could not be updated. Please refresh and try again.');
            await Swal.fire({ icon: data.skipped ? 'warning' : 'success', title: data.skipped ? 'Some tickets were skipped' : 'Tickets updated', text: data.message });
            window.location.reload();
        } catch (error) {
            await Swal.fire({ icon: 'error', title: 'Unable to update tickets', text: error.message || 'Please try again.' });
        } finally { busy = false; }
    }
    window.toggleSelectAll = group => toggle(group);
    window.toggleAllReports = master => toggle('active', master);
    window.toggleAllResolvedReports = master => toggle('resolved', master);
    window.toggleAllArchived = master => toggle('archive', master);
    window.toggleAllDeleted = master => toggle('deleted', master);
    window.toggleAllDeletedReports = master => toggle('deleted', master);
    window.deletedReportsToggleSelectAll = () => toggle('deleted', document.getElementById('deletedReportsSelectAll'));
    window.deletedReportsBulkRestore = () => run('deleted', 'restore-deleted');
    window.getSelectedReports = () => Array.from(selections.active);
    window.getSelectedResolvedReports = () => Array.from(selections.resolved);
    for (const group of Object.keys(groups)) window['update' + group[0].toUpperCase() + group.slice(1) + 'BulkActions'] = () => update(group);
    const actions = {
        batchArchiveSelected: ['active', 'archive'], batchSoftDeleteSelected: ['active', 'soft-delete'],
        batchArchiveResolved: ['resolved', 'archive'], batchSoftDeleteResolved: ['resolved', 'soft-delete'],
        batchRestoreArchived: ['archive', 'restore'], batchSoftDeleteArchived: ['archive', 'soft-delete'],
        batchRestoreDeleted: ['deleted', 'restore-deleted'], batchPermanentDeleteSelected: ['deleted', 'permanent-delete'], batchPermanentDelete: ['deleted', 'permanent-delete']
    };
    for (const [name, args] of Object.entries(actions)) window[name] = () => run(...args);
    document.addEventListener('DOMContentLoaded', () => Object.keys(groups).forEach(update));
})();
