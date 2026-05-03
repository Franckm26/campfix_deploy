<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'name',
        'type',
        'location',
        'capacity',
        'description',
        'status',
        'managed_by',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    public static function types(): array
    {
        return [
            'room'    => 'Room',
            'court'   => 'Court',
            'avr'     => 'AVR',
            'library' => 'Library',
            'lab'     => 'Computer Laboratory',
            'other'   => 'Other',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available'         => 'success',
            'unavailable'       => 'secondary',
            'under_maintenance' => 'warning',
            default             => 'secondary',
        };
    }
}
