<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['eventId']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['eventId']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="card mt-4" id="discussionCard">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Event Discussion</h5>
    </div>
    <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="chatContainer">
        <div id="chatMessages" class="mb-3">
            <div class="text-center text-muted py-3">
                <i class="fas fa-spinner fa-spin"></i> Loading discussions...
            </div>
        </div>
    </div>
    <div class="card-footer">
        <form id="chatForm" class="d-flex gap-2">
            <?php echo csrf_field(); ?>
            <input type="text" id="chatMessage" class="form-control" placeholder="Type your message..." maxlength="1000">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const eventId = <?php echo e($eventId); ?>;
    const chatMessages = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const chatMessage = document.getElementById('chatMessage');
    const currentUserId = <?php echo e(auth()->id()); ?>;
    
    // Load discussions
    loadDiscussions();
    
    function loadDiscussions() {
        fetch(`/events/${eventId}/discussions`)
            .then(response => response.json())
            .then(data => {
                displayDiscussions(data);
            })
            .catch(error => {
                console.error('Error loading discussions:', error);
                chatMessages.innerHTML = '<div class="text-center text-muted py-3">Failed to load discussions</div>';
            });
    }
    
    function displayDiscussions(discussions) {
        if (discussions.length === 0) {
            chatMessages.innerHTML = '<div class="text-center text-muted py-3">No discussions yet. Start the conversation!</div>';
            return;
        }
        
        chatMessages.innerHTML = discussions.map(discussion => {
            const isOwn = discussion.user_id === currentUserId;
            const time = new Date(discussion.created_at).toLocaleString();
            
            return `
                <div class="mb-3 d-flex ${isOwn ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="d-flex flex-column ${isOwn ? 'align-items-end' : 'align-items-start'}" style="max-width: 75%;">
                        <div class="text-muted small mb-1">
                            <strong>${discussion.user ? discussion.user.name : 'Unknown'}</strong>
                            <span class="ms-1">${time}</span>
                        </div>
                        <div class="p-2 rounded ${isOwn ? 'bg-primary text-white' : 'bg-light text-dark'}" style="word-wrap: break-word;">
                            ${escapeHtml(discussion.message)}
                        </div>
                        ${isOwn ? `<button class="btn btn-link btn-sm text-danger p-0 mt-1" onclick="deleteDiscussion(${discussion.id})">Delete</button>` : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        // Scroll to bottom
        const chatContainer = document.getElementById('chatContainer');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatMessage.value.trim();
        if (!message) return;
        
        fetch(`/events/${eventId}/discussions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            chatMessage.value = '';
            loadDiscussions();
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        });
    });
    
    // Delete discussion function
    window.deleteDiscussion = function(discussionId) {
        if (!confirm('Are you sure you want to delete this message?')) return;
        
        fetch(`/discussions/${discussionId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            loadDiscussions();
        })
        .catch(error => {
            console.error('Error deleting message:', error);
            alert('Failed to delete message.');
        });
    };
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/components/event-discussion-chat.blade.php ENDPATH**/ ?>