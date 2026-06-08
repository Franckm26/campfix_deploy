# Notification System Enhancement - Implementation Guide

## Overview
Enhanced the notification system to match Moodle-style notifications with modal view, profile pictures, and navigation.

## ✅ Completed Changes

### 1. NotificationController.php
Added new methods:
- `show($id)` - Get notification detail with prev/next navigation
- `markAsRead($id)` - API endpoint to mark as read
- Updated `destroy()` to return JSON

### 2. Routes (web.php)
Added new routes:
```php
Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markRead');
```

### 3. Layout File (app.blade.php)
Enhanced notification dropdown with:
- Profile avatars for senders
- Unread status dots
- Click to open modal (instead of redirect)
- Delete button with AJAX
- "View all notifications" footer link

## 🔄 Still Needs to Be Added

### 1. Add Notification Modal HTML
Add this before `</body>` tag in `resources/views/layouts/app.blade.php`:

```html
<!-- Notification Detail Modal -->
<div class="modal fade" id="notificationDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification from <span id="modalSenderName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="notification-modal-sender">
                    <img id="modalSenderAvatar" src="" alt="" class="rounded-circle" width="50" height="50">
                    <div>
                        <div class="fw-bold" id="modalSenderNameFull"></div>
                        <small class="text-muted" id="modalNotificationTime"></small>
                    </div>
                </div>
                <hr>
                <div class="notification-modal-subject">
                    <strong>Subject:</strong> <span id="modalSubject"></span>
                </div>
                <div class="notification-modal-message mt-3">
                    <div id="modalMessage"></div>
                </div>
                <div class="notification-modal-action mt-3" id="modalActionBtn" style="display:none;">
                    <a href="#" class="btn btn-primary" id="modalViewLink">View Submission</a>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="modalPrevBtn" onclick="navigateNotification('prev')">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="modalNextBtn" onclick="navigateNotification('next')">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-danger btn-sm" id="modalDeleteBtn" onclick="deleteCurrentNotification()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
```

### 2. Add JavaScript Functions
Add this before closing `</body>` tag:

```javascript
<script>
let currentNotificationId = null;
let currentNotificationData = null;

// Open notification modal
async function openNotificationModal(notificationId, event) {
    if (event) {
        event.stopPropagation();
        // Check if delete button was clicked
        if (event.target.closest('.notification-delete-btn')) {
            return;
        }
    }
    
    currentNotificationId = notificationId;
    
    try {
        const response = await fetch(`/notifications/${notificationId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to fetch notification');
        
        const data = await response.json();
        currentNotificationData = data;
        
        // Populate modal
        document.getElementById('modalSenderName').textContent = data.sender?.name || 'System';
        document.getElementById('modalSenderNameFull').textContent = data.sender?.name || 'System';
        document.getElementById('modalNotificationTime').textContent = data.created_at_human;
        document.getElementById('modalSubject').textContent = data.title;
        document.getElementById('modalMessage').innerHTML = data.message;
        
        // Set avatar
        const avatar = document.getElementById('modalSenderAvatar');
        if (data.sender?.profile_picture) {
            avatar.src = data.sender.profile_picture;
            avatar.style.display = 'block';
        } else {
            avatar.style.display = 'none';
        }
        
        // Show/hide action button
        if (data.url) {
            document.getElementById('modalActionBtn').style.display = 'block';
            document.getElementById('modalViewLink').href = data.url;
        } else {
            document.getElementById('modalActionBtn').style.display = 'none';
        }
        
        // Enable/disable navigation buttons
        document.getElementById('modalPrevBtn').disabled = !data.prev_id;
        document.getElementById('modalNextBtn').disabled = !data.next_id;
        
        // Mark as read
        await fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        // Update UI to show as read
        const notifItem = document.querySelector(`[onclick*="${notificationId}"]`);
        if (notifItem) {
            notifItem.classList.remove('unread');
            const statusDot = notifItem.querySelector('.notification-status-dot');
            if (statusDot) statusDot.remove();
        }
        
        // Update badge count
        updateNotificationCount();
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('notificationDetailModal'));
        modal.show();
        
        // Close notification dropdown
        document.getElementById('notificationDropdown').classList.remove('show');
        
    } catch (error) {
        console.error('Error loading notification:', error);
        alert('Failed to load notification');
    }
}

// Navigate to prev/next notification
async function navigateNotification(direction) {
    const targetId = direction === 'prev' ? currentNotificationData.prev_id : currentNotificationData.next_id;
    if (targetId) {
        await openNotificationModal(targetId);
    }
}

// Delete current notification in modal
async function deleteCurrentNotification() {
    if (!currentNotificationId) return;
    
    if (!confirm('Are you sure you want to delete this notification?')) return;
    
    try {
        const response = await fetch(`/notifications/${currentNotificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to delete notification');
        
        // Remove from list
        const notifItem = document.querySelector(`[onclick*="${currentNotificationId}"]`);
        if (notifItem) notifItem.remove();
        
        // Update count
        updateNotificationCount();
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('notificationDetailModal')).hide();
        
        // Show success message
        showToast('Notification deleted successfully');
        
    } catch (error) {
        console.error('Error deleting notification:', error);
        alert('Failed to delete notification');
    }
}

// Delete notification from list
async function deleteNotification(notificationId, event) {
    event.stopPropagation();
    
    if (!confirm('Are you sure you want to delete this notification?')) return;
    
    try {
        const response = await fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to delete notification');
        
        // Remove from list
        const notifItem = event.target.closest('.notification-item');
        if (notifItem) notifItem.remove();
        
        // Update count
        updateNotificationCount();
        
        showToast('Notification deleted successfully');
        
    } catch (error) {
        console.error('Error deleting notification:', error);
        alert('Failed to delete notification');
    }
}

// Update notification badge count
async function updateNotificationCount() {
    try {
        const response = await fetch('/api/notifications/unread-count');
        const data = await response.json();
        const badge = document.querySelector('.notification-badge');
        if (data.count > 0) {
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            }
        } else {
            if (badge) badge.style.display = 'none';
        }
        
        // Update header count
        const headerCount = document.querySelector('.notification-count');
        if (headerCount) headerCount.textContent = data.count;
    } catch (error) {
        console.error('Error updating notification count:', error);
    }
}

// Simple toast notification
function showToast(message) {
    // You can use your existing toast/notification system
    // Or implement a simple one
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;top:20px;right:20px;background:#28a745;color:white;padding:12px 20px;border-radius:6px;z-index:9999;box-shadow:0 4px 6px rgba(0,0,0,0.1);';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function openAllNotifications(event) {
    event.preventDefault();
    // You can create a dedicated notifications page or expand the dropdown
    alert('View all notifications page - implement as needed');
}
</script>
```

### 3. Add CSS Styles
Add to the `<style>` section in `resources/views/layouts/app.blade.php`:

```css
/* Enhanced Notification Styles */
.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 600;
}

.notification-count {
    background: #3b82f6;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.notification-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    position: relative;
    transition: background 0.2s;
}

.notification-item:hover {
    background: #f9fafb;
}

.notification-item.unread {
    background: #eff6ff;
}

.notification-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    margin-right: 12px;
}

.notification-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.notification-avatar-initial {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #3b82f6;
    color: white;
    font-weight: 600;
    font-size: 16px;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-sender {
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
    margin-bottom: 4px;
}

.notification-message {
    font-size: 13px;
    color: #64748b;
    margin: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.notification-time {
    font-size: 11px;
    color: #94a3b8;
}

.notification-status-dot {
    width: 8px;
    height: 8px;
    background: #3b82f6;
    border-radius: 50%;
    flex-shrink: 0;
    margin: 0 8px;
}

.notification-delete-btn {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.notification-delete-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

.notification-footer {
    padding: 12px 16px;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.notification-footer a {
    color: #3b82f6;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.notification-footer a:hover {
    text-decoration: underline;
}

/* Notification Modal Styles */
.notification-modal-sender {
    display: flex;
    align-items: center;
    gap: 12px;
}

.notification-modal-subject {
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
}

.notification-modal-message {
    line-height: 1.6;
}

/* Dark theme support for notification modal */
[data-theme="dark"] .notification-item.unread {
    background: #1e3a5f;
}

[data-theme="dark"] .notification-sender {
    color: #e0e0e0;
}

[data-theme="dark"] .notification-message {
    color: #aaa;
}

[data-theme="dark"] .notification-modal-subject {
    background: #1e1e38;
}

[data-theme="dark"] .notification-footer {
    border-top-color: #2a2a45;
}

[data-theme="dark"] .notification-count {
    background: #4f6ef7;
}
```

## Testing

1. Test notification list display with profile pictures
2. Test clicking a notification opens the modal
3. Test navigation between notifications (prev/next)
4. Test delete functionality both from list and modal
5. Test unread status indicators
6. Test notification count badge updates
7. Test mark as read functionality
8. Test dark theme compatibility

## Next Steps

After implementing these changes:
1. Update notification classes to include `sender_id` in data array
2. Add a dedicated "All Notifications" page if needed
3. Consider adding notification filters (unread, type, etc.)
4. Add notification sound/toast when new notifications arrive
5. Consider adding notification preferences per type

## Files Modified

1. `app/Http/Controllers/NotificationController.php` - ✅ Done
2. `routes/web.php` - ✅ Done
3. `resources/views/layouts/app.blade.php` - ⚠️ Partially done (need to add modal HTML, JS, and CSS)

## Implementation Priority

1. **High**: Add the modal HTML and JavaScript functions
2. **High**: Add the CSS styles
3. **Medium**: Test all functionality
4. **Low**: Add "View all notifications" page
