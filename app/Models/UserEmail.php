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
        'email_id'
    ];

    public function users(){
        return $this->belongsToMany(User::class);
    }
    public function emailMaster(){
        return $this->belongsToMany(EmailMaster::class);
    }
}
