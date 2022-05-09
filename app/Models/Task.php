<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_LEAD = 'lead';
    const TYPE_TASK = 'task';
    
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'subject', 'description', 'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, TaskUser::class, 'task_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
}
