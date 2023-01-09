<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Proposal extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    public $table = 'proposal';
    protected $fillable = [
        'task_id',
        'proposal_date',
        'client_name',
        'concerned_person',
        'address',
        'subject',
        'prephase',
        'internal_notes',
        'footer_title',
        'footer_description',
        

    ];

    public function proposalSection()
    {
        return $this->hasMany(ProposalSection::class);
    }
    public function proposalFees()
    {
        return $this->hasMany(ProposalFees::class);
    }

}
