<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_bill_items', function (Blueprint $table) {
            $table->double('discount_per', 8, 2)->default(0.00)->after('cgst');
            $table->double('discount', 8, 2)->default(0.00)->after('discount_per');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_bill_items', function (Blueprint $table) {
            //
        });
    }
};
