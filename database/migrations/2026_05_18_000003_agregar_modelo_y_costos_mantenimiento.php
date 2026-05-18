<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activos', function (Blueprint $table) {
            $table->string('modelo')->nullable()->after('nombre');
        });

        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->decimal('costo_servicio', 10, 2)->default(0)->after('tipo');
            $table->boolean('es_preventivo')->default(false)->after('costo_servicio');
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->dropColumn(['costo_servicio', 'es_preventivo']);
        });

        Schema::table('activos', function (Blueprint $table) {
            $table->dropColumn('modelo');
        });
    }
};
