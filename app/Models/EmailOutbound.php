<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailOutbound extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $table = 'emails_outbound_setting';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'mail_transport',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'status',
    ];

}
