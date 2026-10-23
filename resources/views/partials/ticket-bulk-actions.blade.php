<script>
window.ticketBulkConfig = {
    resource: @json($bulkResource),
    csrf: @json(csrf_token()),
    urls: {
        @foreach(['archive', 'restore', 'soft-delete', 'restore-deleted', 'permanent-delete'] as $bulkAction)
        @json($bulkAction): @json(route('tickets.batch', ['resource' => $bulkResource, 'action' => $bulkAction])),
        @endforeach
    }
};
</script>
<script src="{{ asset('js/ticket-bulk-actions.js') }}"></script>
