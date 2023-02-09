<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class SubtaskTemplateBody extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    public $table = 'subtask_template_body';
    protected $fillable = [
        'subtask_template_id',
        'steps_body',
        'attachment_url',

    ];
}
