<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailOutbound extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $table = 'email_outbound';
}
