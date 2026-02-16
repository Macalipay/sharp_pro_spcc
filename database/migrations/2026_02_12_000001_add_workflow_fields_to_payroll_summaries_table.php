<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkflowFieldsToPayrollSummariesTable extends Migration
{
    public function up()
    {
        Schema::table('payroll_summaries', function (Blueprint $table) {
            $table->integer('workflow_status')->default(0)->after('status');
            $table->unsignedBigInteger('submitted_by')->nullable()->after('workflow_status');
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('submitted_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('payment_submitted_by')->nullable()->after('approved_at');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_submitted_by');
        });
    }

    public function down()
    {
        Schema::table('payroll_summaries', function (Blueprint $table) {
            $table->dropColumn([
                'workflow_status',
                'submitted_by',
                'submitted_at',
                'approved_by',
                'approved_at',
                'payment_submitted_by',
                'payment_submitted_at',
            ]);
        });
    }
}
