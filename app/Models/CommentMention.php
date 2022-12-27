<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentMention extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_comment_id', 'user_id',
    ];

    public function comment()
    {
        return $this->belongsTo(TaskComment::class);
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
