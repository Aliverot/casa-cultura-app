<?php

use App\Models\Activo;
use App\Models\AlertaOperativa;
use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use App\Models\User;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('records maintenance and releases the instrument automatically', function () {
    Carbon::setTestNow('2026-04-27 15:30:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100003',
        'nombre' => 'Cello de prueba',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Mantenimiento',
        'horas_uso' => 28.75,
        'limite_mantenimiento' => 20,
    ]);

    $response = $this->actingAs($user)->post(route('mantenimientos.store'), [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Limpieza profunda',
        'costo_servicio' => 250,
        'estado_condicion' => 'Excelente',
        'es_preventivo' => 1,
        'observaciones' => 'Se ajusto el puente y se cambio una cuerda.',
    ]);

    $response->assertRedirect(route('mantenimientos.index'));

    $this->assertDatabaseHas('mantenimientos', [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Limpieza profunda',
        'costo_servicio' => 250,
        'es_preventivo' => true,
    ]);

    expect($activo->refresh()->estado_actual)->toBe('Disponible')
        ->and($activo->estado_condicion)->toBe('Excelente')
        ->and($activo->horas_uso)->toBe(0.0);
});

it('resolves fragility alerts when maintenance is completed', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100012',
        'nombre' => 'Violin fragil',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Mantenimiento',
        'estado_condicion' => 'En reparacion',
        'horas_uso' => 12,
        'limite_mantenimiento' => 20,
    ]);

    $alerta = AlertaOperativa::create([
        'id_activo' => $activo->id_activo,
        'tipo' => 'Fragilidad/Mal Uso',
        'titulo' => 'Alerta de Fragilidad/Mal Uso',
        'descripcion' => 'Prueba',
        'estado' => 'Pendiente',
    ]);

    $this->actingAs($user)->post(route('mantenimientos.store'), [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Revision general',
        'estado_condicion' => 'Excelente',
    ])->assertRedirect(route('mantenimientos.index'));

    expect($alerta->refresh()->estado)->toBe('Resuelta');
});

it('creates seasonal preparation alerts when recent demand increases', function () {
    Carbon::setTestNow('2026-05-18 10:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100009',
        'nombre' => 'Saxofon de prueba',
        'categoria' => 'Instrumentos de Viento',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 100,
    ]);

    foreach ([45, 44] as $dias) {
        $prestamo = Prestamo::create([
            'id_usuario' => $user->id_usuario,
            'fecha_salida' => now()->subDays($dias),
            'fecha_devolucion_prevista' => now()->subDays($dias - 1),
            'nombre_solicitante' => 'Alumno Demo',
            'contacto_solicitante' => 'MAT-'.$dias,
            'condiciones_entrega' => 'En buen estado',
            'estado_pago' => 'Sin cargos',
        ]);

        DetallePrestamo::create([
            'id_prestamo' => $prestamo->id_prestamo,
            'id_activo' => $activo->id_activo,
            'estado_salida' => 'Prestado',
        ]);
    }

    foreach ([10, 9, 8] as $dias) {
        $prestamo = Prestamo::create([
            'id_usuario' => $user->id_usuario,
            'fecha_salida' => now()->subDays($dias),
            'fecha_devolucion_prevista' => now()->subDays($dias - 1),
            'nombre_solicitante' => 'Alumno Demo',
            'contacto_solicitante' => 'MAT-'.$dias,
            'condiciones_entrega' => 'En buen estado',
            'estado_pago' => 'Sin cargos',
        ]);

        DetallePrestamo::create([
            'id_prestamo' => $prestamo->id_prestamo,
            'id_activo' => $activo->id_activo,
            'estado_salida' => 'Prestado',
        ]);
    }

    $this->actingAs($user)->get(route('mantenimientos.index'))->assertOk();

    $this->assertDatabaseHas('alertas_operativas', [
        'id_activo' => null,
        'tipo' => 'Preparacion de Temporada',
        'estado' => 'Pendiente',
    ]);
});

it('creates seasonal preparation alerts from base mexican dates without history', function () {
    Carbon::setTestNow('2026-08-20 10:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100010',
        'nombre' => 'Jarana de prueba',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 100,
    ]);

    $this->actingAs($user)->get(route('mantenimientos.index'))->assertOk();

    $this->assertDatabaseHas('alertas_operativas', [
        'id_activo' => null,
        'tipo' => 'Preparacion de Temporada',
        'estado' => 'Pendiente',
    ]);
});
