<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MailboxAttachment extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    public $table= 'mailbox_attachments';

      /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mailbox_id', 'attachment_url', 'attachment_name', 'folder'
    ];


    public function mailbox()
    {
        return $this->belongsToMany(Mailbox::class);
    }

}
