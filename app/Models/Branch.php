<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
class Branch extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'bussiness_name',
        'bussiness_type',
        'pan_number',
        'state_code',
        'bank_id',
        'address',
        'website',
        'logo',
        'mobile'
    ];
    public function task()
    {
        return $this->hasMany(Task::class);
    }
    public function bankDetails(){
        return $this->belongsTo(bankDetails::class,'bank_id');
    }
  
}
