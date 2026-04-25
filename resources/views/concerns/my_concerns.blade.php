@extends('layouts.app')

@section('page_title')
<h2>My Submitted Concerns</h2>
@endsection

@section('content')
<div class="container-fluid px-2 px-md-3">

    @if($concerns->isEmpty())
        <p class="text-center text-gray-500">You have not submitted any concerns yet.</p>
    @else
        <div class="row g-3">
            @foreach($concerns as $concern)
                <div class="col-12 col-md-6">
                    <div class="bg-white shadow-md rounded-2xl p-3 p-md-4 h-100">
                        @if($concern->title)
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $concern->title }}</h3>
                        @endif
                        <p class="text-gray-600 mb-1" style="font-size: 14px;"><span class="font-semibold">Location:</span> {{ $concern->location }}</p>
                        <p class="text-gray-600 mb-1" style="font-size: 14px;"><span class="font-semibold">Category:</span> {{ $concern->category }}</p>
                        <p class="text-gray-700 mt-2" style="font-size: 14px;">{{ $concern->description }}</p>
                        <p class="text-gray-400 text-sm mt-2 mt-md-3">
                            {{ $concern->created_at->format('M d, Y g:i A') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Floating + button -->
    <button onclick="openModal()" 
        class="fixed bottom-8 right-8 bg-yellow-400 hover:bg-yellow-500 text-white w-14 h-14 md:w-16 md:h-16 rounded-full shadow-lg text-2xl md:text-3xl flex items-center justify-center z-50">
        <i class="fas fa-plus"></i>
    </button>

    <!-- Modal -->
    <div id="submitConcernModal" class="custom-modal">
        <div class="modal-content" style="max-width: 90%;">
            <span class="close" onclick="closeModal()" style="position:absolute; top:10px; right:15px; font-size:24px; cursor:pointer;">&times;</span>
            <h3 class="mb-3">Submit New Concern</h3>

            <form action="{{ route('concerns.store') }}" method="POST">
                @csrf
                <div class="mb-2">
                    <input type="text" name="title" placeholder="Title (Optional)" class="form-control">
                </div>
                <div class="mb-2">
                    <input type="text" name="location" placeholder="Location" required class="form-control">
                </div>
                <div class="mb-2">
                    <input type="text" name="category" placeholder="Category" required class="form-control">
                </div>
                <div class="mb-2">
                    <textarea name="description" placeholder="Description" required class="form-control" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary btn-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<style>
@media (max-width: 576px) {
    .custom-modal {
        padding: 10px !important;
        align-items: flex-end !important;
    }
    
    .custom-modal .modal-content {
        border-radius: 15px 15px 0 0 !important;
        max-height: 85vh;
    }
    
    .fixed.bottom-8.right-8 {
        bottom: 20px !important;
        right: 20px !important;
    }
}
</style>
