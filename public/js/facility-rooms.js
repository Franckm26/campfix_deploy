(function () {
    'use strict';
    let settings = {}, sequence = 0, nextIndex = 1;
    const byId = id => document.getElementById(id);
    function entries() {
        const first = byId('modal_area_of_use');
        if (!first) return [];
        return [{location: first.value, reason: '', select: first, message: byId('first-room-availability')},
            ...Array.from(document.querySelectorAll('[data-facility-room]')).map(row => ({
                location: row.querySelector('select').value, reason: row.querySelector('textarea').value.trim(),
                select: row.querySelector('select'), reasonInput: row.querySelector('textarea'), message: row.querySelector('[role="status"]')
            }))];
    }
    function sync() {
        byId('first-room-location').value = byId('modal_area_of_use').value;
        const chosen = entries().map(room => room.location).filter(Boolean);
        entries().forEach(room => Array.from(room.select.options).forEach(option => {
            option.disabled = !!option.value && option.value !== room.location && chosen.includes(option.value);
        }));
        byId('add-facility-room').disabled = entries().length >= 10;
    }
    function validate() {
        sync();
        const rooms = entries(), seen = new Set();
        for (const [index, room] of rooms.entries()) {
            if (!room.location) return 'Select a room for every room entry.';
            if (seen.has(room.location.toLowerCase())) return 'Select each room only once.';
            seen.add(room.location.toLowerCase());
            if (index > 0 && !room.reason) return 'Explain why you need each additional room.';
            if (room.reason.length > 500) return 'Each room reason must be 500 characters or fewer.';
            if (room.message.dataset.available === 'false') return room.location + ' is unavailable for the selected time.';
            if (room.message.dataset.available === 'checking') return 'Please wait for the room availability checks.';
        }
        return null;
    }
    async function checkAvailability() {
        sync();
        const current = ++sequence;
        const date = byId('modal_event_date').value, start = byId('modal_start_time').value, end = byId('modal_end_time').value;
        await Promise.all(entries().map(async room => {
            room.message.textContent = '';
            delete room.message.dataset.available;
            if (!room.location || !date || !start || !end || end <= start) return;
            room.message.textContent = 'Checking availability...';
            room.message.className = 'small mt-2 text-muted';
            room.message.dataset.available = 'checking';
            try {
                let result;
                if (settings.snapshot) {
                    const status = settings.statuses[room.location] || 'available';
                    const conflict = settings.bookings.some(booking => booking.location === room.location && booking.event_date === date && booking.start_time < end && booking.end_time > start);
                    result = {available: status !== 'unavailable' && !conflict,
                        warning: status === 'under_maintenance' ? 'Under maintenance; you may still request this room.' : null};
                } else {
                    const response = await fetch('/api/check-room-availability?' + new URLSearchParams({location: room.location, event_date: date, start_time: start, end_time: end}), {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
                    if (!response.ok) throw new Error('Availability check failed');
                    result = await response.json();
                }
                if (current !== sequence) return;
                room.message.dataset.available = String(result.available);
                room.message.className = 'small mt-2 ' + (!result.available ? 'text-danger' : result.warning ? 'text-warning' : 'text-success');
                room.message.textContent = room.location + ': ' + (!result.available ? (result.reason || 'Unavailable for the selected time.') : (result.warning || 'Available for the selected time.'));
            } catch (_) {
                if (current !== sequence) return;
                delete room.message.dataset.available;
                room.message.className = 'small mt-2 text-warning';
                room.message.textContent = 'Availability could not be checked. It will be checked again when you submit.';
            }
        }));
    }
    function addRoom(initial = {}) {
        if (entries().length >= 10) return;
        const index = nextIndex++, row = document.createElement('div');
        row.className = 'border rounded p-3 mt-3'; row.dataset.facilityRoom = '';
        const select = byId('modal_area_of_use').cloneNode(true);
        select.id = 'facility-room-' + index; select.name = 'rooms[' + index + '][location]'; select.value = initial.location || ''; select.required = true;
        select.classList.remove('is-invalid');
        const label = document.createElement('label'); label.htmlFor = select.id; label.className = 'form-label'; label.textContent = 'Room *';
        const reason = document.createElement('textarea'); reason.id = 'facility-room-reason-' + index;
        reason.name = 'rooms[' + index + '][reason]'; reason.className = 'form-control'; reason.rows = 2; reason.maxLength = 500; reason.required = true;
        reason.placeholder = 'Explain why you need this room';
        reason.value = initial.reason || '';
        const reasonLabel = document.createElement('label'); reasonLabel.htmlFor = reason.id; reasonLabel.className = 'form-label mt-2'; reasonLabel.textContent = 'Reason for using this room *';
        const message = document.createElement('div'); message.setAttribute('role', 'status');
        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'btn btn-outline-danger btn-sm mt-2'; remove.textContent = 'Remove room';
        remove.addEventListener('click', () => { row.remove(); checkAvailability(); });
        select.addEventListener('change', checkAvailability);
        row.append(label, select, reasonLabel, reason, message, remove);
        byId('additional-facility-rooms').appendChild(row); sync();
    }
    window.FacilityRooms = {
        init(config) {
            settings = config;
            byId('add-facility-room').addEventListener('click', () => addRoom());
            if (Array.isArray(config.oldRooms) && config.oldRooms.length) {
                byId('modal_area_of_use').value = config.oldRooms[0].location || '';
                config.oldRooms.slice(1, 10).forEach(room => addRoom(room));
            }
            byId('eventRequestForm').addEventListener('reset', () => {
                byId('additional-facility-rooms').replaceChildren();
                setTimeout(checkAvailability, 0);
            });
            sync();
        },
        validate, checkAvailability,
        preview() {
            const rooms = entries();
            byId('preview_location').textContent = rooms.map(room => room.location).join(', ');
            byId('preview_area_of_use').textContent = rooms.map(room => room.location).join(', ');
            const reasons = rooms.filter(room => room.reason);
            byId('preview_description').textContent = byId('modal_description').value + (reasons.length ? '\n\nRoom use:\n' + reasons.map(room => room.location + ': ' + room.reason).join('\n') : '');
            byId('preview_description').style.whiteSpace = 'pre-wrap';
        }
    };
}());
