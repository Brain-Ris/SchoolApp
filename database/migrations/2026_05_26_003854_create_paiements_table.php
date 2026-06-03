<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->integer('montant'); // Le montant versé lors de cette transaction
            
            // RELATION ELOQUENT : Lié à l'élève qui effectue le paiement
            $table->foreignId('eleve_id')->constrained('eleves')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};