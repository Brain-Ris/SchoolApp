<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TABLE PIVOT : classe_matiere
 * 
 * Cette table fait le lien entre une classe et ses matières.
 * Pour chaque ligne : une classe + une matière + un barème (10 ou 20) + le prof assigné.
 * 
 * Exemple d'une ligne :
 *   classe_id = 1 (CP1)
 *   matiere_id = 3 (Mathématiques)
 *   bareme = 20  ← note max pour les maths en CP1
 *   user_id = 5  ← le prof qui gère CP1
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classe_matiere', function (Blueprint $table) {
            $table->id();

            // Clé étrangère vers la table 'classes'
            $table->foreignId('classe_id')
                  ->constrained('classes')
                  ->onDelete('cascade'); // Si on supprime la classe, on supprime aussi les liaisons

            // Clé étrangère vers la table 'matieres'
            $table->foreignId('matiere_id')
                  ->constrained('matieres')
                  ->onDelete('cascade'); // Si on supprime la matière, on supprime aussi les liaisons

            // Barème : note maximum pour cette matière dans cette classe
            // Valeur obligatoire : soit 10, soit 20
            $table->unsignedTinyInteger('bareme')->default(20);

            // Le prof assigné à cette classe (optionnel au niveau base de données)
            // user_id = null veut dire "pas encore de prof assigné"
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null'); // Si on supprime le prof, on met null (pas d'erreur)

            $table->timestamps();

            // Empêcher d'associer deux fois la même matière à la même classe
            $table->unique(['classe_id', 'matiere_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classe_matiere');
    }
};
