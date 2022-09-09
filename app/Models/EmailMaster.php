<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMaster extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $table = 'emails_settings';

    protected $fillable = [
        'email',
        'status',
    ];

public function emailInbound(){

    return $this->hasOne(EmailInbound::class,'id','id');
}

    public function emailOutbound(){

        return $this->hasOne(EmailOutbound::class,'id','id');
    
    }


}
