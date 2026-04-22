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
        Schema::create('proforma_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proforma_invoice_id');
            $table->unsignedBigInteger('product_id');
            $table->double('rate', 8, 2);
            $table->double('qty', 8, 2);
            $table->double('igst_per', 8, 2)->default(0.00);
            $table->double('igst', 8, 2)->default(0.00);
            $table->double('sgst_per', 8, 2)->default(0.00);
            $table->double('sgst', 8, 2)->default(0.00);
            $table->double('cgst_per', 8, 2)->default(0.00);
            $table->double('cgst', 8, 2)->default(0.00);
            $table->double('amount', 10, 2);
            $table->double('return_qty', 8, 2)->default(0.00);
            $table->double('return_igst', 8, 2)->default(0.00);
            $table->double('return_sgst', 8, 2)->default(0.00);
            $table->double('return_cgst', 8, 2)->default(0.00);
            $table->double('return_amount', 10, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('proforma_invoice_id')->references('id')->on('proforma_invoices')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoice_items');
    }
};
