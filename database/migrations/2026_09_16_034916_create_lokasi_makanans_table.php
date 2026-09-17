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
        Schema::create('lokasi_makanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_id')
                ->constrained('lokasis')
                ->cascadeOnDelete(); 
            $table->foreignId('makanan_id')
                ->constrained('makanans')
                ->cascadeOnDelete();
            $table->longText('deskripsi')->nullable();
            $table->text('foto')->nullable();
            $table->timestamps();
            $table->unique(['lokasi_id', 'makanan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_makanans');
    }
};
