<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('matricule_national')->nullable();
            $table->string('matricule_interne')->nullable();
            $table->string('nom');
            $table->string('prenoms');
            $table->date('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->enum('sexe', ['M', 'F']);
            $table->string('photo')->nullable();
            $table->string('contact_parent')->nullable();
            $table->string('statut')->default('N');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->timestamps();

            $table->index(['classe_id', 'nom']);
            $table->unique(['matricule_national', 'classe_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('etudiants');
    }
};
