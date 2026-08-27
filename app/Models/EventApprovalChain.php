<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventApprovalChain extends Model
{
    protected $fillable = ['event_education_level_id', 'event_request_type_id', 'approval_roles'];

    protected $casts = ['approval_roles' => 'array'];

    public function educationLevel()
    {
        return $this->belongsTo(EventEducationLevel::class, 'event_education_level_id');
    }

    public function requestType()
    {
        return $this->belongsTo(EventRequestType::class, 'event_request_type_id');
    }
}
