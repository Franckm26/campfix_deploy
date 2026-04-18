/* Dashboard JavaScript - Calendar initialization */

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: '/events',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: ''
            },
            dayHeaderFormat: { weekday: 'short' },
            slotMinTime: '00:00:00',
            slotMaxTime: '24:00:00',
            aspectRatio: 1,
        });
        calendar.render();
    }
});
