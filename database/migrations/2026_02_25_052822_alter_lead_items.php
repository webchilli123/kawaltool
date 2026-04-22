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
        Schema::table('lead_items', function (Blueprint $table) {
            $table->dropForeign('lead_items_item_id_foreign');

            $table->dropColumn('item_id');
        });

        DB::statement("ALTER TABLE `lead_items` ADD `product_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `lead_id`");
        DB::statement("ALTER TABLE `lead_items` ADD CONSTRAINT `lead_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
