<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("SELECT setval(pg_get_serial_sequence('orders', 'id'), GREATEST(4559, (SELECT MAX(id) FROM orders) + 1), false)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("SELECT setval(pg_get_serial_sequence('orders', 'id'), (SELECT MAX(id) FROM orders), true)");
    }
};
