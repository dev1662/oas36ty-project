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
        'date',
        'u_date',
        'folder',
        'references',
        'in_reply_to',
        'is_parent',
        'bccaddress',
        'ccaddress',
        'to_replyEmails',
        'task_lead_id',
        'plainText_messages',
        'task_id'
    ];

    public function attachments_file()
    {
        return $this->hasMany(MailboxAttachment::class);
       }

       public function userMailbox()
       {
           return $this->hasOne(UserMailbox::class);
          }

    public function taskStatus(){
        return $this->belongsTo(Task::class,'task_id');
       
    }
    public function task()
    {
        return $this->hasMany(Task::class);
    }
    
}
