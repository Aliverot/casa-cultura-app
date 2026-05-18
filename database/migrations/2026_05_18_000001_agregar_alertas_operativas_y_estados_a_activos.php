<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            $table->decimal('valor_original', 10, 2)->nullable()->after('categoria');
            $table->string('estado_condicion')->default('Excelente')->after('estado_actual');
        });

        Schema::table('detalle_prestamos', function (Blueprint $table) {
            $table->text('contexto_incidente')->nullable()->after('estado_retorno');
            $table->string('entorno_uso')->nullable()->after('contexto_incidente');
            $table->text('accesorios_proteccion')->nullable()->after('entorno_uso');
        });

        Schema::create('alertas_operativas', function (Blueprint $table) {
            $table->id('id_alerta');
            $table->unsignedBigInteger('id_activo')->nullable();
            $table->string('tipo');
            $table->string('titulo');
            $table->text('descripcion');
            $table->json('datos')->nullable();
            $table->string('estado')->default('Pendiente');
            $table->timestamp('fecha_alerta')->useCurrent();
            $table->timestamps();

            $table->foreign('id_activo')->references('id_activo')->on('activos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_operativas');

        Schema::table('detalle_prestamos', function (Blueprint $table) {
            $table->dropColumn(['contexto_incidente', 'entorno_uso', 'accesorios_proteccion']);
        });

        Schema::table('activos', function (Blueprint $table) {
            $table->dropColumn(['valor_original', 'estado_condicion']);
        });
    }
};
