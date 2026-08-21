<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventIntendedUser extends Model {
    protected $fillable = ['name', 'code', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
