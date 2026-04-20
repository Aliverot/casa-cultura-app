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
        Schema::create('detalle_prestamos', function (Blueprint $table) {
            $table->id('id_detalle'); // PK             
            // Llaves foráneas 
            $table->unsignedBigInteger('id_prestamo');
            $table->foreign('id_prestamo')->references('id_prestamo')->on('prestamos')->onDelete('cascade');            
            $table->unsignedBigInteger('id_activo');
            $table->foreign('id_activo')->references('id_activo')->on('activos')->onDelete('cascade');            
            $table->string('estado_salida'); 
            $table->string('estado_retorno')->nullable(); // Nullable porque al salir aún no hay retorno 
            $table->dateTime('fecha_devolucion_real')->nullable(); // Se llena hasta regresar 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_prestamos');
    }
};
