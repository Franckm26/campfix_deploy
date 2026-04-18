<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name'];

    public function concerns()
    {
        return $this->hasMany(Concern::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
