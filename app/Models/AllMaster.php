<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Stancl\Tenancy\Contracts\SyncMaster;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Models\TenantPivot;

class AllMaster extends Model
{
    use HasApiTokens, HasFactory, Notifiable, CentralConnection;

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DECLINED = 'declined';

    // protected $guarded = [];
    //  protected $connection = 'mysql';
    // public $timestamps = false;
    public $table = 'all_masters';
   
    // public $table = 'states';
    protected $fillable = [
        'name',
       ];

public function privileges()
       {
           return $this->hasMany(Privileges::class);
       }
          
}
