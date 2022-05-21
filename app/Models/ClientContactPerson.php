<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientContactPerson extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'name', 'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contactPersonPhones()
    {
        return $this->hasMany(ClientContactPersonPhone::class);
    }

    public function contactPersonEmails()
    {
        return $this->hasMany(ClientContactPersonEmail::class);
    }
}
