<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;


class StatusMaster extends Model implements Auditable
{
    use HasFactory,SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public $table = 'status_masters';
    protected $fillable = [
        'type'
    ];

    public function task()
    {
        return $this->hasMany(Task::class);
    }
}
