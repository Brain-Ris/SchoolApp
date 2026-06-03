<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eleves', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->enum('genre', ['M', 'F']); // Masculin / Féminin
            $table->string('photo')->nullable(); // Contiendra le chemin de l'image
            
            // RELATION ELOQUENT : Clé étrangère liée à la table classes
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eleves');
    }
};