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
        Schema::create('activos', function (Blueprint $table) {
            $table->id('id_activo');
            $table->string('codigo_qr')->unique();
            $table->string('nombre');
            $table->string('categoria');
            $table->string('estado_actual')->default('Disponible');
            $table->float('horas_uso')->default(0);
            $table->float('limite_mantenimiento');
            $table->timestamps(); // Crea los campos fecha de creación y actualización
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activos');
    }
};
