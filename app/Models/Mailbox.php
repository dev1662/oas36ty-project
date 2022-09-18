<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Mailbox extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    public $table = 'mailbox';
    protected $fillable = [
        'message_id',
        'avatar',
        'from_name',
        'from_email',
        'to_email',
        'subject',
        'message',
        'attachments',
        'label',
        'isStarred',
        'type',
        'date'
    ];
    
}
