<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalTemplateSection extends Model
{
    use HasFactory;
    public $table = 'proposal_template_sections';
    protected $fillable = [
        'proposal_template_id',
        'title',
        'description',
    ];
}
