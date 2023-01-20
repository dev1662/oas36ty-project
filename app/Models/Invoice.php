<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Invoice extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    public $incrementing = false;
    public $table = 'invoices';
    protected $fillable = [
        'id',
        'client_id',
        'client_gst_number',
        'state_code',
        'invoice_number',
        'invoice_date',
        'due_date',
        'billing_address',
        'notes',
        'item_details',
        'amount',
        'discount',
        'taxable_amt',
        'igst',
        'igst_amt',
        'sgst',
        'sgst_amt',
        'cgst',
        'cgst_amt',
        'utgst',
        'utgst_amt',
        'sub_total',
        'pocket_expenses',
        'expenses_details',
        'adjustment_amt',
        'total_amt',
        'task_id',
        'proposal_id',
       
    ];

    public function client(){
        return $this->belongsTo(Company::class,'client_id');
    }
    public function proposal(){
        return $this->hasMany(ProposalFees::class,'proposal_id','proposal_id');
    }

}
