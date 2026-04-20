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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id('id_prestamo'); // PK             
            // Llave foránea hacia usuarios 
            $table->unsignedBigInteger('id_usuario'); 
            $table->foreign('id_usuario')->references('id_usuario')->on('users')->onDelete('cascade');
            // Llave foránea hacia activos
            $table->dateTime('fecha_salida')->useCurrent(); // Default: now() 
            $table->dateTime('fecha_devolucion_prevista'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
