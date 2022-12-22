<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMailbox extends Model
{
    use HasFactory;
    public $table = 'user_mailboxes';

    public $fillable = [
        'user_id',
        'mailbox_id',
        'is_read',
        'is_spam',
        'is_trash',
    ];

    public function mailbox()
    {
        return $this->hasMany(Mailbox::class);

    }

    public function users()
    {
        return $this->hasMany(User::class);

    }
    
}
