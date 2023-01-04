<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;
    public $table = 'proposal';
    protected $fillable = [
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

}
