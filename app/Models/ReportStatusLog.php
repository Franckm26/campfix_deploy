<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportStatusLog extends Model
{
    protected $fillable = [
        'report_id',
        'changed_by',
        'old_status',
        'new_status',
        'remarks',
        'changed_at',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
