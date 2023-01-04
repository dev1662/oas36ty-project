<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalTemplate extends Model
{
    use HasFactory;
    public $table = 'proposal_templates';
    protected $fillable = [
        'template_name',
    ];
}
