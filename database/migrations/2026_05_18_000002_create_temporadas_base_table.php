<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('temporadas_base', function (Blueprint $table) {
            $table->id('id_temporada');
            $table->string('nombre');
            $table->string('fecha_inicio', 5);
            $table->string('fecha_fin', 5);
            $table->unsignedSmallInteger('dias_anticipacion')->default(30);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        DB::table('temporadas_base')->insert([
            ['nombre' => 'Ano Nuevo', 'fecha_inicio' => '01-01', 'fecha_fin' => '01-01', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dia de la Constitucion', 'fecha_inicio' => '02-05', 'fecha_fin' => '02-05', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Natalicio de Benito Juarez', 'fecha_inicio' => '03-21', 'fecha_fin' => '03-21', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dia del Trabajo', 'fecha_inicio' => '05-01', 'fecha_fin' => '05-01', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Independencia de Mexico', 'fecha_inicio' => '09-16', 'fecha_fin' => '09-16', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dia de Muertos', 'fecha_inicio' => '11-01', 'fecha_fin' => '11-02', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Revolucion Mexicana', 'fecha_inicio' => '11-20', 'fecha_fin' => '11-20', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Temporada decembrina', 'fecha_inicio' => '12-12', 'fecha_fin' => '01-06', 'dias_anticipacion' => 30, 'activa' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('temporadas_base');
    }
};
