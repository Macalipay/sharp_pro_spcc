<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupportingDocumentToJournalEntries extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'supporting_document')) {
                $table->string('supporting_document')->nullable()->after('auto_reversing_date');
            }
        });
    }

    public function down()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'supporting_document')) {
                $table->dropColumn('supporting_document');
            }
        });
    }
}
