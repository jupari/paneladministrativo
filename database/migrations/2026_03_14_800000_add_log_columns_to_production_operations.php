<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_operations', function (Blueprint $t) {
            $t->date('work_date')->nullable()->after('registered_at');
            $t->string('shift', 10)->nullable()->after('work_date');
            $t->decimal('rejected_qty', 12, 4)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('production_operations', function (Blueprint $t) {
            $t->dropColumn(['work_date', 'shift', 'rejected_qty']);
        });
    }
};
