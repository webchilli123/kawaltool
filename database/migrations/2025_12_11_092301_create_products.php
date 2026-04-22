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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('brand_id');
            $table->string('specification')->nullable();
            $table->string('sku')->nullable();
            $table->string('capacity')->nullable();
            $table->string('material_type')->nullable();
            $table->integer('opening_stock')->nullable();
            $table->integer('current_stock')->nullable();
            $table->integer('min_stock')->nullable();
            $table->integer('max_stock')->nullable();
            $table->string('rack_location')->nullable();
            $table->text('barcode')->nullable();
            $table->text('batch')->nullable();
            $table->decimal('purchase_price')->nullable();
            $table->decimal('last_purchase_price')->nullable();
            $table->decimal('selling_price')->nullable();
            $table->decimal('discount')->nullable();
            $table->decimal('gst')->nullable();
            $table->boolean('is_active');
            $table->boolean('is_returnable');
            $table->bigInteger("created_by")->nullable();
            $table->bigInteger("updated_by")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
