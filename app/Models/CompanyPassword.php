<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CompanyPassword extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    public $table = "company_passwords";
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
        'company_id',
        'compliance',
        'username',
        'password',
        'remarks'
    ];
}
