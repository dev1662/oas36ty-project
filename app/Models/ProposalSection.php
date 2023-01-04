<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalSection extends Model
{
    use HasFactory;
    public $table = 'proposal_sections';
    protected $fillable = [
        'proposal_id',
        'title',
        'description',
    ];
}
