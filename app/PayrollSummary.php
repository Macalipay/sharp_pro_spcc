<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollSummary extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "sequence_title",
        "sequence_no",
        "schedule_type",
        "period_start",
        "payroll_period",
        "pay_date",
        "status",
        'workstation_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function calendar()
    {
        return $this->belongsTo(PayrollCalendar::class, 'sequence_title', 'id');
    }

    public function details()
    {
        return $this->hasMany(PayrollSummaryDetails::class, 'summary_id');
    }
}
