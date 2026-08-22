<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventIntendedUser extends Model {
    protected $fillable = ['name', 'code', 'approval_roles', 'is_active'];
    protected $casts = ['approval_roles' => 'array', 'is_active' => 'boolean'];
}
