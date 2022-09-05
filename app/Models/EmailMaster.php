<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailMaster extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $table = 'email_master';
}
