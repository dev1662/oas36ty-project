<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSignature extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'signature',
        'user_id'
    ];
    
}
