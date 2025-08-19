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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transaksi')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('jenis', ['penjualan', 'pembelian']);
            $table->decimal('total', 12, 2);
            $table->timestamps();
            
            $table->index(['nomor_transaksi', 'jenis']);
            $table->index(['user_id', 'jenis']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
