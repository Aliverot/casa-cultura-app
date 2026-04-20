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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id('id_mantenimiento'); // PK             
            // Llave foránea hacia el activo 
            $table->unsignedBigInteger('id_activo');
            $table->foreign('id_activo')->references('id_activo')->on('activos')->onDelete('cascade');            
            $table->dateTime('fecha_servicio')->useCurrent(); 
            $table->string('tipo'); // Afinación, limpieza, etc. 
            $table->text('observaciones')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
