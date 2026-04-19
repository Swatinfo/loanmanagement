<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_tasks', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('loan_detail_id')
                ->constrained('quotations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('general_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
        });
    }
};
