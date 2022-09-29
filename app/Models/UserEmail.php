<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserEmail extends Model implements Auditable
{
    use HasFactory,SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    
    protected $guarded = [];
    public $table = 'user_emails';

    public $fillable = [
        'user_id',
        'emails_setting_id',
        'inbound_msg_count'
    ];

    public function users(){
        return $this->hasMany(User::class,'id', 'user_id');
    }
    public function EmailsSetting(){
        return $this->hasMany(EmailsSetting::class, 'id', 'emails_setting_id');
    }
}
