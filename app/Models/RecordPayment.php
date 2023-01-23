<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class RecordPayment extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
     const TYPE_DELETE = 'delete';
    const TYPE_DONT_DELETE = 'dont_delete';
    public $table = 'record_payments';
    protected $fillable = [
        'task_id',
        'client_id',
        'payment_mode',
        'branch_id',
        'amount',
        'pay_date',
        'reference_id',
        'notes',
    
    ];

    public function invoice(){
        // return $this->belongsTo(Invoice::class,'invoice_id');

        return $this->belongsToMany(Invoice::class, RecordPaymentInvoice::class, 'record_payment_id', 'invoice_id');

    }
    
    public function recordPayInvoice(){
        return $this->hasMany(RecordPaymentInvoice::class);
    }

}
