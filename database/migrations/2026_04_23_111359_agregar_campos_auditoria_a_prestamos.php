<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prestamos', function (Blueprint $table) {
            // Campos para la Salida (Préstamo)
            $table->string('nombre_solicitante')->nullable();
            $table->string('contacto_solicitante')->nullable(); // Teléfono o Matrícula
            $table->text('condiciones_entrega')->nullable();

            // Campos para el Retorno (Devolución y Cobro)
            $table->text('condiciones_devolucion')->nullable();
            $table->decimal('costo_reparacion', 8, 2)->nullable();
            $table->enum('estado_pago', ['Sin cargos', 'Pendiente', 'Pagado'])->default('Sin cargos');
        });
    }

    public function down()
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn(['nombre_solicitante', 'contacto_solicitante', 'condiciones_entrega', 'condiciones_devolucion', 'costo_reparacion', 'estado_pago']);
        });
    }
};