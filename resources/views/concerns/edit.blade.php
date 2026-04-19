@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Concern</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('concerns.update', $concern->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="" disabled>Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $concern->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location *</label>
                            <input type="text" class="form-control" id="location" name="location" 
                                value="{{ $concern->location }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" 
                                rows="4" required>{{ $concern->description }}</textarea>
                        </div>

                        @if($concern->image_path)
                            <div class="mb-3">
                                <label class="form-label">Current Photo</label>
                                <br>
                                <img src="{{ asset('storage/' . $concern->image_path) }}" 
                                    alt="Current photo" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="image" class="form-label">Change Photo (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" 
                                accept="image/*">
                            <small class="text-muted">Leave empty to keep current photo</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Concern</button>
                            <a href="{{ route('concerns.my') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
