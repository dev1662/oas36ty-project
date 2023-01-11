<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class BankDetails extends Model implements Auditable
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
    public $table = 'bank_details';
    protected $fillable = [
        'account_name',
        'bank_name', 
        'account_number',
        'ifsc_code',
        'swift_code',
        'micr_code',
        'branch_name',
        'account_type'
    ];
}
