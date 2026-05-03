<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'issues'];

    protected $casts = [
        'issues' => 'array',
    ];

    public function concerns()
    {
        return $this->hasMany(Concern::class);
    }
}
