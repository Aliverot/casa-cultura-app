<?php

use App\Models\Activo;
use App\Models\User;
use App\Models\AlertaOperativa;

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

it('syncs standardized condition with operational status when editing inventory', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100011',
        'nombre' => 'Guitarra para reparar',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 50,
    ]);

    $this->actingAs($user)->patch(route('activos.update', $activo->id_activo), [
        'nombre' => 'Guitarra para reparar',
        'categoria' => 'Instrumentos de Cuerda',
        'limite_mantenimiento' => 50,
        'estado_condicion' => 'En reparacion',
    ])->assertRedirect(route('activos.index'));

    expect($activo->refresh()->estado_actual)->toBe('Mantenimiento')
        ->and($activo->estado_condicion)->toBe('En reparacion');
});

it('syncs baja definitiva with operational baja when editing inventory', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100013',
        'nombre' => 'Vestuario para baja',
        'categoria' => 'Vestuario',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 50,
    ]);

    $this->actingAs($user)->patch(route('activos.update', $activo->id_activo), [
        'nombre' => 'Vestuario para baja',
        'categoria' => 'Vestuario',
        'limite_mantenimiento' => 50,
        'estado_condicion' => 'Baja definitiva',
    ])->assertRedirect(route('activos.index'));

    expect($activo->refresh()->estado_actual)->toBe('Baja')
        ->and($activo->estado_condicion)->toBe('Baja definitiva');
});

it('does not allow repair or baja condition while inventory article is loaned', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100014',
        'nombre' => 'Clarinete prestado',
        'categoria' => 'Instrumentos de Viento',
        'estado_actual' => 'No disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 50,
    ]);

    $this->actingAs($user)->from(route('activos.edit', $activo->id_activo))->patch(route('activos.update', $activo->id_activo), [
        'nombre' => 'Clarinete prestado',
        'categoria' => 'Instrumentos de Viento',
        'limite_mantenimiento' => 50,
        'estado_condicion' => 'En reparacion',
    ])->assertSessionHasErrors('estado_condicion');

    expect($activo->refresh()->estado_actual)->toBe('No disponible');
});

it('shows the protected operational state on the edit screen', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100015',
        'nombre' => 'Tarola en mantenimiento',
        'categoria' => 'Percusiones',
        'estado_actual' => 'Mantenimiento',
        'estado_condicion' => 'Excelente',
        'horas_uso' => 15,
        'limite_mantenimiento' => 50,
    ]);

    $this->actingAs($user)
        ->get(route('activos.edit', $activo->id_activo))
        ->assertOk()
        ->assertSee('Estado actual')
        ->assertSee('Mantenimiento')
        ->assertSee('Situación operativa')
        ->assertDontSee('Condición operativa: <span class="font-bold text-anil-900">Excelente</span>', false);
});

it('keeps maintenance status when editing administrative inventory data', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100016',
        'nombre' => 'Flauta en taller',
        'categoria' => 'Instrumentos de Viento',
        'estado_actual' => 'Mantenimiento',
        'estado_condicion' => 'Excelente',
        'horas_uso' => 15,
        'limite_mantenimiento' => 50,
    ]);

    $this->actingAs($user)->patch(route('activos.update', $activo->id_activo), [
        'nombre' => 'Flauta en taller actualizada',
        'categoria' => 'Instrumentos de Viento',
        'limite_mantenimiento' => 55,
    ])->assertRedirect(route('activos.index'));

    expect($activo->refresh()->estado_actual)->toBe('Mantenimiento')
        ->and($activo->estado_condicion)->toBe('En reparacion');
});

it('keeps baja status when editing administrative inventory data', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100017',
        'nombre' => 'Vestuario dado de baja',
        'categoria' => 'Vestuario',
        'estado_actual' => 'Baja',
        'estado_condicion' => 'Excelente',
        'horas_uso' => 15,
        'limite_mantenimiento' => 50,
    ]);

    $this->actingAs($user)->patch(route('activos.update', $activo->id_activo), [
        'nombre' => 'Vestuario dado de baja actualizado',
        'categoria' => 'Vestuario',
        'limite_mantenimiento' => 55,
    ])->assertRedirect(route('activos.index'));

    expect($activo->refresh()->estado_actual)->toBe('Baja')
        ->and($activo->estado_condicion)->toBe('Baja definitiva');
});

it('can resolve an operational alert manually', function () {
    $user = User::factory()->create();
    $alerta = AlertaOperativa::create([
        'tipo' => 'Baja y Adquisicion',
        'titulo' => 'Informe de baja y adquisición',
        'descripcion' => 'Prueba',
        'estado' => 'Pendiente',
    ]);

    $this->actingAs($user)->post(route('alertas.resolver', $alerta->id_alerta))
        ->assertRedirect();

    expect($alerta->refresh()->estado)->toBe('Resuelta');
});
