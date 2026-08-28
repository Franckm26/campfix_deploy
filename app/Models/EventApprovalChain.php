<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventApprovalChain extends Model
{
    protected $fillable = ['event_intended_user_id', 'event_request_type_id', 'approval_roles'];

    protected $casts = ['approval_roles' => 'array'];

    public function intendedUser()
    {
        return $this->belongsTo(EventIntendedUser::class, 'event_intended_user_id');
    }

    public function requestType()
    {
        return $this->belongsTo(EventRequestType::class, 'event_request_type_id');
    }
}
