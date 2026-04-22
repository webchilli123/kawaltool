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
            $table->tinyInteger('challan_type')->nullable()->after('party_id')->comment('0 = non-returnable, 1 = returnable, 2 = fromPi ');
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
