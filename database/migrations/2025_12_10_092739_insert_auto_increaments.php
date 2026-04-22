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
        DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
        DB::statement("TRUNCATE auto_increaments");
        DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
        
        $q = <<<'Q'
            INSERT INTO `auto_increaments` (`id`, `type`, `pattern`, `counter`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
            (1, 'purchase', 'Pur-YY-counter', 0, '2025-01-16 02:02:27', '2025-01-16 02:47:30', 1, 1),
            (2, 'purchase_return', 'Pur-Ret-YY-counter', 0, '2025-01-16 02:44:40', '2025-01-16 02:47:43', 1, 1),
            (3, 'expense', 'E-YY-counter', 0, '2025-01-16 02:45:22', '2025-01-16 02:45:22', 1, NULL),
            (4, 'sale', 'Sale-YY-counter', 0, '2025-01-16 02:45:35', '2025-01-16 02:47:56', 1, 1),
            (5, 'sale_return', 'Sale-Ret-YY-counter', 0, '2025-01-16 02:46:53', '2025-01-16 02:48:09', 1, 1),
            (6, 'payment', 'Pay-YY-counter', 0, '2025-01-16 02:48:33', '2025-01-16 02:48:33', 1, NULL);
        Q;

        DB::statement($q);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
        DB::statement("TRUNCATE auto_increaments");
        DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
    }
};
