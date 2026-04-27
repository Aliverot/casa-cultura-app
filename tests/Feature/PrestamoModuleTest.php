<?php

use App\Models\Activo;
use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use App\Models\User;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('marks an asset as unavailable when a loan is created', function () {
    Carbon::setTestNow('2026-04-27 10:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100001',
        'nombre' => 'Guitarra de prueba',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 100,
    ]);

    $response = $this->actingAs($user)->post(route('prestamos.store'), [
        'id_activo' => $activo->id_activo,
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => 'MAT-001',
        'condiciones_entrega' => 'En perfectas condiciones',
        'fecha_devolucion_prevista' => '2026-04-27T10:00',
    ]);

    $response->assertRedirect(route('activos.index'));

    expect($activo->refresh()->estado_actual)->toBe('No disponible');

    $prestamo = Prestamo::first();

    expect($prestamo)->not->toBeNull()
        ->and($prestamo->nombre_solicitante)->toBe('Alumno Demo');

    $this->assertDatabaseHas('detalle_prestamos', [
        'id_prestamo' => $prestamo->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => 'Prestado',
    ]);
});

it('does not force one hour of use when the return is immediate', function () {
    Carbon::setTestNow('2026-04-27 10:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100002',
        'nombre' => 'Violin de prueba',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 100,
    ]);

    $prestamo = Prestamo::create([
        'id_usuario' => $user->id_usuario,
        'fecha_salida' => now(),
        'fecha_devolucion_prevista' => now()->addHour(),
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => 'MAT-002',
        'condiciones_entrega' => 'En perfectas condiciones',
        'estado_pago' => 'Sin cargos',
    ]);

    $detalle = DetallePrestamo::create([
        'id_prestamo' => $prestamo->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => 'Prestado',
    ]);

    $activo->update(['estado_actual' => 'No disponible']);

    $response = $this->actingAs($user)->post(route('prestamos.devolver', $detalle->id_detalle), [
        'estado_equipo' => 'Buen estado',
        'condiciones_devolucion' => 'Se devuelve al momento y sin danos.',
        'costo_reparacion' => 0,
    ]);

    $response->assertRedirect(route('prestamos.activos'));

    $detalle->refresh();
    $activo->refresh();

    expect($activo->estado_actual)->toBe('Disponible')
        ->and($activo->horas_uso)->toBe(0.0)
        ->and($detalle->estado_retorno)->toBe('En tiempo y forma');
});
