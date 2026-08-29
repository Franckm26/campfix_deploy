@component('mail::message')
# Profile Updated

Hello **{{ $user->name }}**,

Your profile has been successfully updated{{ $updatedBy !== 'self' ? ' by an administrator' : '' }}.

@if(count($changes) > 0)
## Changes Made:

@foreach($changes as $field => $change)
@if($field === 'name')
- **Name:** {{ $change['from'] ?? 'Not set' }} → {{ $change['to'] }}
@elseif($field === 'phone')
- **Phone Number:** {{ $change['from'] ?? 'Not set' }} → {{ $change['to'] ?? 'Removed' }}
@elseif($field === 'backup_email')
- **Backup Email:** {{ $change['from'] ?? 'Not set' }} → {{ $change['to'] ?? 'Removed' }}
@endif
@endforeach
@endif

**Time:** {{ now()->format('M d, Y \a\t g:i A') }}

@if($updatedBy !== 'self')
@component('mail::panel')
**Security Notice:** This change was made by an administrator. If you didn't expect this change, please contact your system administrator immediately.
@endcomponent
@endif

@component('mail::button', ['url' => route('profile.index')])
View Profile
@endcomponent

Thanks,<br>
{{ config('app.name') }} Team

@component('mail::subcopy')
If you're having trouble clicking the "View Profile" button, copy and paste the URL below into your web browser: {{ route('profile.index') }}
@endcomponent
@endcomponent