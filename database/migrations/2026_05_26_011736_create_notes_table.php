<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE : notes
 * 
 * Stocke les notes d'un élève pour un trimestre donné.
 * 
 * Au lieu d'avoir une colonne fixe par matière (lecture, calcul, français...),
 * on utilise une colonne JSON "valeurs" qui stocke toutes les notes en une fois.
 * 
 * Exemple du contenu de la colonne "valeurs" pour un élève :
 * {
 *   "3": 15,    ← matiere_id=3 (Maths), note = 15/20
 *   "5": 8,     ← matiere_id=5 (Dessin), note = 8/10
 *   "7": 14     ← matiere_id=7 (Français), note = 14/20
 * }
 * 
 * Avantage : On n'a pas besoin de modifier la table si on ajoute une nouvelle matière.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();

            // L'élève concerné
            $table->foreignId('eleve_id')
                    ->constrained('eleves')
                    ->onDelete('cascade'); // Si on supprime l'élève, ses notes partent aussi
                    
            $table->foreignId('matiere_id')->constrained()->cascadeOnDelete();
            
            // Numéro du trimestre : 1, 2 ou 3
            $table->unsignedTinyInteger('trimestre');

            // Toutes les notes de l'élève pour ce trimestre
            // Format : { "matiere_id": note_obtenue, ... }
            $table->json('valeurs')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Permet de "supprimer sans vraiment supprimer"

            // Un élève ne peut avoir qu'une seule ligne de notes par matière et par trimestre
            $table->unique(['eleve_id', 'matiere_id', 'trimestre'],'notes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
