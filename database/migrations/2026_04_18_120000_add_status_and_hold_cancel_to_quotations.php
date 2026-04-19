<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('loan_id');
            $table->string('hold_reason_key', 50)->nullable()->after('status');
            $table->text('hold_note')->nullable()->after('hold_reason_key');
            $table->date('hold_follow_up_date')->nullable()->after('hold_note');
            $table->timestamp('held_at')->nullable()->after('hold_follow_up_date');
            $table->foreignId('held_by')->nullable()->after('held_at')
                ->constrained('users')->nullOnDelete();
            $table->string('cancel_reason_key', 50)->nullable()->after('held_by');
            $table->text('cancel_note')->nullable()->after('cancel_reason_key');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_note');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')
                ->constrained('users')->nullOnDelete();

            $table->index('status');
            $table->index('hold_follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['hold_follow_up_date']);
            $table->dropConstrainedForeignId('held_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn([
                'status',
                'hold_reason_key',
                'hold_note',
                'hold_follow_up_date',
                'held_at',
                'cancel_reason_key',
                'cancel_note',
                'cancelled_at',
            ]);
        });
    }
};
