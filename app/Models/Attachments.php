<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Attachments extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'attachment', 'type', 'company_id', 'task_id'
    ];
}
