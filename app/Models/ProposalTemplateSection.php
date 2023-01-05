<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ProposalTemplateSection extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
     const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    public $table = 'proposal_template_sections';
    protected $fillable = [
        'proposal_template_id',
        'title',
        'description',
    ];

    public function proposalTemplate()
    {
        return $this->belongsToMany(ProposalTemplate::class);
    }
}
