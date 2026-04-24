@extends('superadmin.layout')

@section('page_title', 'Categories')

@section('content')

<div class="row g-4">
    {{-- Create Form --}}
    <div class="col-md-4">
        <div class="sa-card">
            <h2 style="font-size:15px;font-weight:600;color:var(--sa-text);margin:0 0 16px">Add Category</h2>
            <form method="POST" action="{{ route('superadmin.categories.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="sa-label">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="sa-input" required placeholder="Category name">
                </div>
                <div class="mb-3">
                    <label class="sa-label">Description</label>
                    <textarea name="description" class="sa-input" rows="3" placeholder="Optional description">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="sa-btn sa-btn-primary w-100" style="justify-content:center">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </form>
        </div>
    </div>

    {{-- Categories List --}}
    <div class="col-md-8">
        <div class="sa-card">
            <h2 style="font-size:15px;font-weight:600;color:var(--sa-text);margin:0 0 16px">
                All Categories <span style="color:var(--sa-muted);font-weight:400">({{ $categories->count() }})</span>
            </h2>
            @forelse($categories as $cat)
            <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--sa-border)" id="cat-{{ $cat->id }}">
                <div style="flex:1">
                    <div style="font-weight:500;color:var(--sa-text)">{{ $cat->name }}</div>
                    @if($cat->description)
                        <div style="font-size:12px;color:var(--sa-muted)">{{ $cat->description }}</div>
                    @endif
                    <div style="font-size:11px;color:var(--sa-muted);margin-top:3px">
                        {{ $cat->concerns_count ?? 0 }} concerns · {{ $cat->reports_count ?? 0 }} reports
                    </div>
                </div>
                <div style="display:flex;gap:6px">
                    <button onclick="editCat({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->description ?? '') }}')"
                            class="sa-btn sa-btn-ghost sa-btn-sm">
                        <i class="fas fa-pen"></i>
                    </button>
                    <form method="POST" action="{{ route('superadmin.categories.delete', $cat->id) }}"
                          onsubmit="return confirm('Delete category \'{{ addslashes($cat->name) }}\'?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <p style="color:var(--sa-muted);font-size:13px">No categories yet.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center">
    <div class="sa-card" style="width:100%;max-width:420px;margin:20px">
        <h3 style="font-size:15px;font-weight:600;color:var(--sa-text);margin:0 0 16px">Edit Category</h3>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="sa-label">Name *</label>
                <input type="text" name="name" id="editName" class="sa-input" required>
            </div>
            <div class="mb-3">
                <label class="sa-label">Description</label>
                <textarea name="description" id="editDesc" class="sa-input" rows="3"></textarea>
            </div>
            <div style="display:flex;gap:10px">
                <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Save</button>
                <button type="button" onclick="closeEdit()" class="sa-btn sa-btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function editCat(id, name, desc) {
    document.getElementById('editForm').action = '/superadmin/categories/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
@endsection
