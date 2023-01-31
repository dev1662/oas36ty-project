<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class UserRole extends Model implements Auditable
{
    use HasFactory,SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    public $table = 'user_roles';
    protected $fillable = [
        'designation_name',
    ];

    public function users()
       {
           return $this->hasMany(User::class,'user_role_id','id');
        //    ->select('id','name','status');
       }
       public function masters()
       {
              return $this->hasMany(UserAccessMaster::class);
        }
        
           public function privileges()
           {
             return $this->hasMany(UserAccessPrivileges::class);
           
            }
       

}
