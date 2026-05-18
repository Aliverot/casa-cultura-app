<?php

use App\Models\Activo;
use App\Models\User;

it('updates an inventory article', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100004',
        'nombre' => 'Trompeta original',
        'categoria' => 'Instrumentos de Viento',
        'estado_actual' => 'Disponible',
        'horas_uso' => 4,
        'limite_mantenimiento' => 60,
    ]);

    $response = $this->actingAs($user)->patch(route('activos.update', $activo->id_activo), [
        'nombre' => 'Trompeta actualizada',
        'modelo' => 'Yamaha YTR',
        'categoria' => 'Instrumentos de Viento',
        'estado_condicion' => 'Funcional con detalles',
        'limite_mantenimiento' => 75,
    ]);

    $response->assertRedirect(route('activos.index'));

    expect($activo->refresh()->nombre)->toBe('Trompeta actualizada')
        ->and($activo->modelo)->toBe('Yamaha YTR')
        ->and($activo->estado_condicion)->toBe('Funcional con detalles')
        ->and($activo->limite_mantenimiento)->toBe(75.0);
});

it('marks an inventory article as baja without deleting history', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100005',
        'nombre' => 'Bateria de prueba',
        'categoria' => 'Percusiones',
        'estado_actual' => 'Disponible',
        'horas_uso' => 10,
        'limite_mantenimiento' => 90,
    ]);

    $response = $this->actingAs($user)->post(route('activos.baja', $activo->id_activo));

    $response->assertRedirect(route('activos.index'));

    expect($activo->refresh()->estado_actual)->toBe('Baja');
});
