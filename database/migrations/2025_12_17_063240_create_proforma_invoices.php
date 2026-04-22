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
        Schema::create('proforma_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('pi_no')->unique();
            $table->date('date');
            $table->unsignedBigInteger('party_id')->nullable();
            $table->unsignedBigInteger('complaint_id')->nullable();
            $table->double('amount', 10, 2)->default(0.00);
            $table->double('freight', 8, 2)->default(0.00);
            $table->double('discount_per', 8, 2)->default(0.00);
            $table->double('discount', 8, 2)->default(0.00);
            $table->double('igst', 8, 2)->default(0.00);
            $table->double('sgst', 8, 2)->default(0.00);
            $table->double('cgst', 8, 2)->default(0.00);
            $table->double('payable_amount', 12, 2)->default(0.00);
            $table->enum('status', ['draft', 'sent', 'approved', 'converted'])->default('draft');
            $table->string('narration', 255)->nullable();
            $table->string('comments', 512)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('party_id')->references('id')->on('parties')->onDelete('restrict');
            $table->foreign('complaint_id')->references('id')->on('complaints')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoices');
    }
};
