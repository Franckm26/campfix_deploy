(function () {
    'use strict';
    let rows = [], roles = {}, token = null, page = 0, busy = false, generation = 0;
    const pageSize = 50, byId = id => document.getElementById(id);
    function errors(messages) {
        const box = byId('staffReviewErrors');
        box.replaceChildren(); box.hidden = !messages.length;
        messages.forEach(message => { const line = document.createElement('div'); line.textContent = message; box.appendChild(line); });
    }
    function render() {
        page = Math.min(page, Math.max(0, Math.ceil(rows.length / pageSize) - 1));
        const body = byId('staffReviewRows'); body.replaceChildren();
        rows.slice(page * pageSize, (page + 1) * pageSize).forEach(row => {
            const tr = document.createElement('tr');
            ['name', 'email'].forEach(key => {
                const td = document.createElement('td'), input = document.createElement('input');
                input.type = key === 'email' ? 'email' : 'text'; input.value = row[key]; input.className = 'form-control';
                input.maxLength = 255; input.required = true; input.setAttribute('aria-label', key === 'name' ? 'Name' : 'Email');
                input.addEventListener('input', () => { row[key] = input.value; });
                td.appendChild(input);
                if (key === 'email' && row.warning) { const warning = document.createElement('small'); warning.className = 'text-warning'; warning.textContent = row.warning; td.appendChild(warning); }
                tr.appendChild(td);
            });
            const roleCell = document.createElement('td'), select = document.createElement('select');
            select.className = 'form-select'; select.setAttribute('aria-label', 'Role');
            Object.entries(roles).forEach(([value, label]) => { const option = document.createElement('option'); option.value = value; option.textContent = label; select.appendChild(option); });
            select.value = row.role; select.addEventListener('change', () => { row.role = select.value; }); roleCell.appendChild(select); tr.appendChild(roleCell);
            const action = document.createElement('td'), remove = document.createElement('button');
            remove.type = 'button'; remove.className = 'btn btn-sm btn-outline-danger'; remove.textContent = 'Remove';
            remove.addEventListener('click', () => { rows = rows.filter(item => item !== row); render(); }); action.appendChild(remove); tr.appendChild(action);
            body.appendChild(tr);
        });
        byId('staffReviewCount').textContent = rows.length + ' staff user(s)' + (rows.length ? ' · Page ' + (page + 1) + ' of ' + Math.ceil(rows.length / pageSize) : '');
        byId('staffReviewPrevious').disabled = page === 0;
        byId('staffReviewNext').disabled = (page + 1) * pageSize >= rows.length;
        byId('staffReviewConfirm').disabled = !rows.length;
    }
    async function send(url, payload, json = false) {
        const form = byId('userImportForm');
        const headers = {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value};
        if (json) headers['Content-Type'] = 'application/json';
        const response = await fetch(url, {method: 'POST', headers, body: json ? JSON.stringify(payload) : payload});
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
            const message = Object.values(data.errors || {}).flat().join('\n') || data.message || data.error
                || (response.status === 413 ? 'The upload exceeds the 2 MB limit.' : 'Unable to process staff import. Please try again.');
            throw new Error(message);
        }
        return data;
    }
    function reset() {
        generation++; rows = []; token = null; page = 0;
        byId('staffImportReview').style.display = 'none'; errors([]);
    }
    async function prepare(form) {
        if (busy || !byId('importFileInput').files.length) return;
        reset(); const current = generation; busy = true;
        const button = form.querySelector('#importStep2 button[type="submit"]');
        button.disabled = true; button.textContent = 'Reading staff file...';
        try {
            const data = await send(byId('staffImportReview').dataset.previewUrl, new FormData(form));
            if (current !== generation) return;
            rows = data.rows; roles = data.roles; token = data.token;
            byId('importStep2').style.display = 'none'; byId('staffImportReview').style.display = 'block';
            byId('dot2').classList.remove('active'); byId('dot2').classList.add('done'); byId('dot3').classList.add('active');
            render();
        } catch (error) {
            if (current === generation) await Swal.fire({icon: 'error', title: 'Unable to read staff file', text: error.message});
        } finally { busy = false; button.disabled = false; button.textContent = 'Review Staff'; }
    }
    async function submit(form) {
        if (busy) return;
        if (!token) return prepare(form);
        const issues = [], seen = new Set();
        rows.forEach((row, index) => {
            if (!row.name.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(row.email.trim())) issues.push('Row ' + (index + 1) + ': enter a name and valid email.');
            const email = row.email.trim().toLowerCase();
            if (seen.has(email)) issues.push('Row ' + (index + 1) + ': email is repeated in this list.');
            seen.add(email);
        });
        if (!rows.length) issues.push('Keep at least one staff user to import.');
        errors(issues); if (issues.length) return;
        busy = true; byId('staffReviewConfirm').disabled = true;
        Swal.fire({title: 'Importing reviewed staff...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading()});
        try {
            const data = await send(byId('staffImportReview').dataset.confirmUrl, {
                preview_token: token, rows: rows.map(({row_id, name, email, role}) => ({row_id, name, email, role})),
                archive_folder_name: form.querySelector('[name="archive_folder_name"]').value,
            }, true);
            await Swal.fire({icon: 'success', title: 'Import complete', text: data.message});
            window.location.assign(data.redirect);
        } catch (error) {
            errors(error.message.split('\n'));
            await Swal.fire({icon: 'error', title: 'Check the staff list', text: error.message});
        } finally { busy = false; byId('staffReviewConfirm').disabled = !rows.length; }
    }
    window.StaffImportReview = {submit, reset};
    document.addEventListener('DOMContentLoaded', () => {
        const form = byId('userImportForm'); if (!form) return;
        byId('importFileInput').addEventListener('change', () => { if (byId('importRoleInput').value === 'staff') prepare(form); });
        byId('staffReviewBack').addEventListener('click', () => { reset(); importGoTo(2); });
        byId('staffReviewPrevious').addEventListener('click', () => { page--; render(); });
        byId('staffReviewNext').addEventListener('click', () => { page++; render(); });
        byId('importModal').addEventListener('hidden.bs.modal', reset);
    });
}());
