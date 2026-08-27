<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventEducationLevel extends Model
{
    protected $fillable = ['name', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function approvalChains()
    {
        return $this->hasMany(EventApprovalChain::class);
    }
}
