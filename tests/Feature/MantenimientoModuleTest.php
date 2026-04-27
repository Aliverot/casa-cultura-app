<?php

use App\Models\Activo;
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
        'observaciones' => 'Se ajusto el puente y se cambio una cuerda.',
    ]);

    $response->assertRedirect(route('mantenimientos.index'));

    $this->assertDatabaseHas('mantenimientos', [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Limpieza profunda',
    ]);

    expect($activo->refresh()->estado_actual)->toBe('Disponible')
        ->and($activo->horas_uso)->toBe(0.0);
});
