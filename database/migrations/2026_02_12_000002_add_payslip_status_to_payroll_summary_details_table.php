<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPayslipStatusToPayrollSummaryDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('payroll_summary_details', function (Blueprint $table) {
            $table->string('payslip_status')->default('FOR SENDING')->after('status');
            $table->timestamp('payslip_sent_at')->nullable()->after('payslip_status');
            $table->unsignedBigInteger('payslip_sent_by')->nullable()->after('payslip_sent_at');
        });
    }

    public function down()
    {
        Schema::table('payroll_summary_details', function (Blueprint $table) {
            $table->dropColumn(['payslip_status', 'payslip_sent_at', 'payslip_sent_by']);
        });
    }
}

