<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventRequestType extends Model {
    protected $fillable = ['name', 'requires_department', 'approval_roles', 'is_active'];
    protected $casts = ['requires_department' => 'boolean', 'approval_roles' => 'array', 'is_active' => 'boolean'];
}
