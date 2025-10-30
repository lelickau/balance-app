<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });

        if (env('DB_CONNECTION') === 'pgsql') {
            DB::statement('ALTER TABLE balances ADD CONSTRAINT check_balance_nonnegative CHECK (amount >= 0);');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
