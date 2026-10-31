<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nota_penerimaan_barangs', function (Blueprint $table) {
            $table->id();
            $table->string('penerima');
            $table->date('tanggal');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_penerimaan_barangs');
    }
};
