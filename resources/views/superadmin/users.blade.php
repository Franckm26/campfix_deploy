@extends('superadmin.layout')

@section('page_title', 'All Users')

@section('content')

{{-- Filters --}}
<div class="sa-card mb-4">
    <form method="GET" action="{{ route('superadmin.users') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:180px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Name, email, department…">
        </div>
        <div style="min-width:150px">
            <label class="sa-label">Role</label>
            <select name="role" class="sa-input">
                <option value="">All Roles</option>
                @foreach(['student','faculty','maintenance','mis','school_admin','building_admin','academic_head','program_head','principal_assistant','superadmin'] as $r)
                    <option value="{{ $r }}" {{ $role === $r ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($r)) }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width:140px">
            <label class="sa-label">Status</label>
            <select name="status" class="sa-input">
                <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="archived" {{ $status === 'archived' ? 'selected' : '' }}>Archived</option>
                <option value="deleted"  {{ $status === 'deleted'  ? 'selected' : '' }}>Deleted</option>
                <option value="locked"   {{ $status === 'locked'   ? 'selected' : '' }}>Locked</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('superadmin.users') }}" class="sa-btn sa-btn-ghost">Reset</a>
        </div>
        <a href="{{ route('superadmin.users.create') }}" class="sa-btn sa-btn-primary" style="margin-left:auto">
            <i class="fas fa-plus"></i> New User
        </a>
    </form>
</div>

{{-- Table --}}
<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="sa-avatar" style="width:28px;height:28px;font-size:11px;flex-shrink:0">{{ strtoupper(substr($user->name,0,1)) }}</div>
                            <div>
                                <div style="font-weight:500">{{ $user->name }}</div>
                                @if($user->is_superadmin)
                                    <span class="sa-badge sa-badge-purple" style="font-size:10px">Superadmin</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--sa-muted)">{{ $user->email }}</td>
                    <td>
                        <span class="sa-badge sa-badge-gray">{{ str_replace('_',' ',ucfirst($user->role ?? 'N/A')) }}</span>
                    </td>
                    <td style="color:var(--sa-muted)">{{ $user->department ?? '—' }}</td>
                    <td>
                        @if($user->is_deleted)
                            <span class="sa-badge sa-badge-red">Deleted</span>
                        @elseif($user->is_archived)
                            <span class="sa-badge sa-badge-yellow">Archived</span>
                        @elseif($user->locked_until && $user->locked_until > now())
                            <span class="sa-badge sa-badge-red">Locked</span>
                        @else
                            <span class="sa-badge sa-badge-green">Active</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                            <a href="{{ route('superadmin.users.edit', $user->uuid) }}" class="sa-btn sa-btn-ghost sa-btn-sm" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            @if($user->is_deleted)
                                <form method="POST" action="{{ route('superadmin.users.restore', $user->uuid) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-ghost sa-btn-sm" title="Restore" style="color:#4ade80">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                </form>
                            @endif
                            @if($user->locked_until && $user->locked_until > now())
                                <form method="POST" action="{{ route('superadmin.users.unlock', $user->uuid) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="sa-btn sa-btn-ghost sa-btn-sm" title="Unlock" style="color:#fbbf24">
                                        <i class="fas fa-lock-open"></i>
                                    </button>
                                </form>
                            @endif
                            @if(!$user->is_superadmin && $user->id !== auth()->id())
                                <form method="POST" action="{{ route('superadmin.users.delete', $user->uuid) }}" style="display:inline"
                                      onsubmit="return confirm('Permanently delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--sa-muted);padding:32px">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        {{ $users->links('vendor.pagination.superadmin') }}
    </div>
    @endif
</div>

@endsection
