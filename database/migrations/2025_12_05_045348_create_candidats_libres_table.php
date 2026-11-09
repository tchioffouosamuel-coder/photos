<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('candidats_libres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->string('nom');
            $table->date('date_naissance');
            $table->string('lieu_naissance');
            $table->enum('sexe', ['M', 'F']);
            $table->string('photo')->nullable();
            $table->timestamps();
            
            $table->index(['classe_id', 'nom']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidats_libres');
    }
};