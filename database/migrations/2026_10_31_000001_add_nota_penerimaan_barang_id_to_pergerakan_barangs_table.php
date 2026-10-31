<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pergerakan_barangs', function (Blueprint $table) {
            $table->foreignId('nota_penerimaan_barang_id')
                ->nullable()
                ->after('penerima')
                ->constrained('nota_penerimaan_barangs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pergerakan_barangs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('nota_penerimaan_barang_id');
        });
    }
};
