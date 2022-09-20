<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class EmailsSetting extends Model implements Auditable
{
    use HasFactory,SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];
    public $table = 'emails_settings';

    protected $fillable = [
        'email',
        'inbound_status',
        'outbound_status',

    ];

public function emailInbound(){

    return $this->hasOne(EmailInbound::class,'id','id');
}

    public function emailOutbound(){

        return $this->hasOne(EmailOutbound::class,'id','id');
    
    }

    public function userEmails(){
        return $this->hasOne(UserEmail::class);
    }
}
