<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'delivery_date',
        'po_date',
        'contact_no',
        'reference',
        'terms',
        'due_date',
        'order_no',
        'tax_type',
        'subtotal',
        'total_with_tax',
        'delivery_instruction',
        'status',
        'split_type',
        'prepared_by',
        'prepared_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'received_by',
        'received_at',
        'workstation_id',
        'created_by',
        'updated_by',
        'oldpo',
        'project_id'
    ];

    public function details() {
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_id', 'id');
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function prepared_by() {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function reviewed_by() {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approved_by() {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function received_by() {
        return $this->belongsTo(User::class, 'received_by');
    }
    
    public function projects() {
        return $this->hasMany(ProjectSplit::class, 'po_id', 'id');
    }

    public function credits() {
        return $this->hasMany(CreditNote::class, 'po_id', 'id');
    }
    
    public function discount() {
        return $this->hasOne(Discount::class, 'po_id')->where('po_type', 'all');
    }
}
