<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            // Nom de la classe (ex: CP1, CE1, CM2...)
            $table->string('nom'); 
            // Frais de scolarité associés à cette classe (ex: 50000)
            $table->integer('frais'); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};