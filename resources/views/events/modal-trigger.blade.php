@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <p class="mb-3">Opening facility request form...</p>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-open the event request modal
    var modal = new bootstrap.Modal(document.getElementById('eventRequestModal'));
    modal.show();

    // Redirect to dashboard when modal is closed
    document.getElementById('eventRequestModal').addEventListener('hidden.bs.modal', function () {
        window.location.href = '/dashboard';
    });
});
</script>
@endsection