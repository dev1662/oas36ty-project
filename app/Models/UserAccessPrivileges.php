<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class UserAccessPrivileges extends Model implements Auditable
{
    use HasFactory,SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    public $table = 'user_access_privileges';
    protected $fillable = [
        'user_role_id',
        'user_access_master_id',
        'all_master_id',
        'privilege_id',
    ];
}
