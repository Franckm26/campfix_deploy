/* Shared Application JavaScript */

// Modal functions
function openModal(){
    document.getElementById("submitConcernModal").classList.add("active");
}
window.openModal = openModal;

function closeModal(){
    document.getElementById("submitConcernModal").classList.remove("active");
}
window.closeModal = closeModal;

// Close modal when clicking outside the modal content
document.addEventListener('click', function(event) {
    const modal = document.getElementById('submitConcernModal');
    const modalContent = modal?.querySelector('.modal-content');
    if (modal && modal.classList.contains('active') && modalContent) {
        if (!modalContent.contains(event.target) && event.target !== modal) {
            closeModal();
        }
    }
});

// Sidebar toggle
function toggleSidebar(){
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
    
    // Prevent body scroll when sidebar is open on mobile
    if (window.innerWidth < 992) {
        document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
    }
}
window.toggleSidebar = toggleSidebar;

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const menuBtn = document.querySelector('.mobile-menu-btn');
    
    if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('show')) {
        if (!sidebar.contains(event.target) && (!menuBtn || !menuBtn.contains(event.target))) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (window.innerWidth >= 992) {
        if (sidebar) sidebar.classList.remove('show');
        if (overlay) overlay.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// Notification dropdown toggle
function toggleNotification(event){
    event.stopPropagation();
    const dropdown = document.getElementById('notificationDropdown');
    const userDropdown = document.getElementById('userDropdownMenu');
    
    // Close user dropdown if open
    if (userDropdown && userDropdown.classList.contains('show')) {
        userDropdown.classList.remove('show');
    }
    
    // Toggle notification dropdown
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
    } else {
        dropdown.classList.add('show');
    }
}
window.toggleNotification = toggleNotification;

// User dropdown toggle
function toggleDropdown(event){
    event.stopPropagation();
    const dropdown = document.getElementById('userDropdownMenu');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    // Close notification dropdown if open
    if (notificationDropdown && notificationDropdown.classList.contains('show')) {
        notificationDropdown.classList.remove('show');
    }
    
    // Toggle user dropdown
    if (dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
    } else {
        dropdown.classList.add('show');
    }
}
window.toggleDropdown = toggleDropdown;

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdownMenu');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const userIcon = document.querySelector('.user-icon');
    const notificationBell = document.querySelector('.notification-bell');
    
    if (userDropdown && userIcon && !userIcon.contains(event.target)) {
        userDropdown.classList.remove('show');
    }
    
    if (notificationDropdown && notificationBell && !notificationBell.contains(event.target)) {
        notificationDropdown.classList.remove('show');
    }
});

// Navigation dropdown toggle (for Events menu, etc.)
document.addEventListener('DOMContentLoaded', function() {
    // Handle all nav dropdown toggles
    const navToggles = document.querySelectorAll('[data-nav-toggle]');
    
    navToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const dropdown = this.closest('.nav-dropdown');
            
            if (dropdown) {
                // Toggle the open class
                dropdown.classList.toggle('open');
            }
        });
    });
});

// Dark/Light Mode Toggle
function toggleTheme() {
    const body = document.body;
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    
    body.classList.toggle('dark-mode');
    
    // Save preference to localStorage
    const isDark = body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    // Update UI
    if (themeIcon) {
        themeIcon.classList.toggle('fa-moon', !isDark);
        themeIcon.classList.toggle('fa-sun', isDark);
    }
    if (themeText) {
        themeText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    }
    
    // Save to database via AJAX
    saveThemePreference(isDark ? 'dark' : 'light');
}

// Save theme preference to database
function saveThemePreference(theme) {
    fetch('/profile/update-theme', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ theme: theme })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Theme preference saved:', data);
    })
    .catch(error => {
        console.log('Theme saved to localStorage only');
    });
}

// Load saved theme on page load
document.addEventListener('DOMContentLoaded', function() {
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    
    // First check localStorage, then check system preference
    let savedTheme = localStorage.getItem('theme');
    
    // If no saved theme, detect system preference
    if (!savedTheme) {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            savedTheme = 'dark';
        } else {
            savedTheme = 'light';
        }
    }
    
    const isDark = savedTheme === 'dark';
    
    if (isDark) {
        document.body.classList.add('dark-mode');
    }
    
    if (themeIcon) {
        themeIcon.classList.toggle('fa-moon', !isDark);
        themeIcon.classList.toggle('fa-sun', isDark);
    }
    if (themeText) {
        themeText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
    }
});

// Listen for system theme changes
if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        // Only apply if user hasn't set a preference
        if (!localStorage.getItem('theme')) {
            const isDark = e.matches;
            document.body.classList.toggle('dark-mode', isDark);
            
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            if (themeIcon) {
                themeIcon.classList.toggle('fa-moon', !isDark);
                themeIcon.classList.toggle('fa-sun', isDark);
            }
            if (themeText) {
                themeText.textContent = isDark ? 'Light Mode' : 'Dark Mode';
            }
        }
    });
}
