<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalFees extends Model
{
    use HasFactory;
    public $table = 'proposal_fees';
    protected $fillable = [
        'proposal_id',
        'description',
        'amount',
    
    ];
}
