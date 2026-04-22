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
        Schema::table('sale_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('party_id');
            
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_bills', function (Blueprint $table) {
            //
        });
    }
};
