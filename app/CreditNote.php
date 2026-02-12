<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    protected $fillable = [
        "project_id",
        "po_id",
        "chart_id",
        "status",
        "amount",
        "particulars",
        "workstation_id",
        "created_by",
        "updated_by",
    ];

    public function project() {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function po() {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function chart() {
        return $this->belongsTo(ChartOfAccount::class, 'chart_id');
    }
}
