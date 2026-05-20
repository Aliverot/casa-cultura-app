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
        'contexto_incidente' => 'No debe guardarse',
        'entorno_uso' => 'Ensayo',
        'accesorios_proteccion' => 'Funda',
    ]);

    $response->assertRedirect(route('prestamos.activos'));

    $detalle->refresh();
    $activo->refresh();

    expect($activo->estado_actual)->toBe('Disponible')
        ->and($activo->horas_uso)->toBe(0.0)
        ->and($detalle->estado_retorno)->toBe('En tiempo y forma')
        ->and($detalle->contexto_incidente)->toBeNull()
        ->and($detalle->entorno_uso)->toBeNull()
        ->and($detalle->accesorios_proteccion)->toBeNull();
});

it('marks an instrument as extraviado when the return cannot be completed physically', function () {
    Carbon::setTestNow('2026-04-27 12:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100006',
        'nombre' => 'Clarinete de prueba',
        'categoria' => 'Instrumentos de Viento',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 100,
    ]);

    $prestamo = Prestamo::create([
        'id_usuario' => $user->id_usuario,
        'fecha_salida' => now()->subHours(2),
        'fecha_devolucion_prevista' => now()->addHour(),
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => 'MAT-003',
        'condiciones_entrega' => 'En buen estado al salir',
        'estado_pago' => 'Sin cargos',
    ]);

    $detalle = DetallePrestamo::create([
        'id_prestamo' => $prestamo->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => 'Prestado',
    ]);

    $activo->update(['estado_actual' => 'No disponible']);

    $response = $this->actingAs($user)->post(route('prestamos.devolver', $detalle->id_detalle), [
        'estado_equipo' => 'Extraviado',
        'condiciones_devolucion' => 'El usuario reporta que el instrumento fue extraviado.',
        'costo_reparacion' => 3500,
    ]);

    $response->assertRedirect(route('prestamos.activos'));

    expect($activo->refresh()->estado_actual)->toBe('Extraviado')
        ->and($detalle->refresh()->estado_retorno)->toBe('Extraviado')
        ->and($prestamo->refresh()->estado_pago)->toBe('Pendiente');
});

it('requires incident details and creates a fragility alert after recurrent damage', function () {
    Carbon::setTestNow('2026-04-27 12:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100007',
        'nombre' => 'Viola de prueba',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 100,
    ]);

    for ($i = 1; $i <= 2; $i++) {
        $prestamoHistorico = Prestamo::create([
            'id_usuario' => $user->id_usuario,
            'fecha_salida' => now()->subDays(20 + $i),
            'fecha_devolucion_prevista' => now()->subDays(19 + $i),
            'nombre_solicitante' => 'Alumno Demo',
            'contacto_solicitante' => 'MAT-00'.$i,
            'condiciones_entrega' => 'En buen estado',
            'condiciones_devolucion' => 'Regreso con dano',
            'costo_reparacion' => 100,
            'estado_pago' => 'Pendiente',
        ]);

        DetallePrestamo::create([
            'id_prestamo' => $prestamoHistorico->id_prestamo,
            'id_activo' => $activo->id_activo,
            'estado_salida' => 'Prestado',
            'estado_retorno' => 'Danado',
            'fecha_devolucion_real' => now()->subDays(19 + $i),
        ]);
    }

    $prestamo = Prestamo::create([
        'id_usuario' => $user->id_usuario,
        'fecha_salida' => now()->subHours(2),
        'fecha_devolucion_prevista' => now()->addHour(),
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => 'MAT-010',
        'condiciones_entrega' => 'En buen estado al salir',
        'estado_pago' => 'Sin cargos',
    ]);

    $detalle = DetallePrestamo::create([
        'id_prestamo' => $prestamo->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => 'Prestado',
    ]);

    $activo->update(['estado_actual' => 'No disponible']);

    $this->actingAs($user)->post(route('prestamos.devolver', $detalle->id_detalle), [
        'estado_equipo' => 'Danado',
        'condiciones_devolucion' => 'Regresa con una fisura.',
        'costo_reparacion' => 150,
    ])->assertSessionHasErrors(['contexto_incidente', 'entorno_uso', 'accesorios_proteccion']);

    $response = $this->actingAs($user)->post(route('prestamos.devolver', $detalle->id_detalle), [
        'estado_equipo' => 'Danado',
        'condiciones_devolucion' => 'Regresa con una fisura.',
        'costo_reparacion' => 150,
        'contexto_incidente' => 'Se golpeo durante el traslado.',
        'entorno_uso' => 'Transporte',
        'accesorios_proteccion' => 'La funda venia abierta.',
    ]);

    $response->assertRedirect(route('prestamos.activos'));

    expect($activo->refresh()->estado_actual)->toBe('Mantenimiento')
        ->and($activo->estado_condicion)->toBe('En reparacion');

    $this->assertDatabaseHas('alertas_operativas', [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Fragilidad/Mal Uso',
        'estado' => 'Pendiente',
    ]);

    $this->assertDatabaseHas('detalle_prestamos', [
        'id_detalle' => $detalle->id_detalle,
        'contexto_incidente' => 'Se golpeo durante el traslado.',
        'entorno_uso' => 'Transporte',
        'accesorios_proteccion' => 'La funda venia abierta.',
    ]);

    $this->actingAs($user)->get(route('prestamos.historial'))
        ->assertOk()
        ->assertSee('Condiciones de retorno')
        ->assertSee('Regresa con una fisura.')
        ->assertSee('Se golpeo durante el traslado.')
        ->assertSee('Transporte')
        ->assertSee('La funda venia abierta.');
});

it('creates a replacement report when repairs exceed the configured value limit', function () {
    Carbon::setTestNow('2026-04-27 12:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100008',
        'nombre' => 'Tambor de prueba',
        'categoria' => 'Percusiones',
        'valor_original' => 1000,
        'estado_actual' => 'Disponible',
        'horas_uso' => 0,
        'limite_mantenimiento' => 100,
    ]);

    $prestamoHistorico = Prestamo::create([
        'id_usuario' => $user->id_usuario,
        'fecha_salida' => now()->subDays(20),
        'fecha_devolucion_prevista' => now()->subDays(19),
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => 'MAT-020',
        'condiciones_entrega' => 'En buen estado',
        'condiciones_devolucion' => 'Regreso roto',
        'costo_reparacion' => 500,
        'estado_pago' => 'Pendiente',
    ]);

    DetallePrestamo::create([
        'id_prestamo' => $prestamoHistorico->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => 'Prestado',
        'estado_retorno' => 'Danado',
        'fecha_devolucion_real' => now()->subDays(19),
    ]);

    $prestamo = Prestamo::create([
        'id_usuario' => $user->id_usuario,
        'fecha_salida' => now()->subHours(2),
        'fecha_devolucion_prevista' => now()->addHour(),
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => 'MAT-021',
        'condiciones_entrega' => 'En buen estado al salir',
        'estado_pago' => 'Sin cargos',
    ]);

    $detalle = DetallePrestamo::create([
        'id_prestamo' => $prestamo->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => 'Prestado',
    ]);

    $activo->update(['estado_actual' => 'No disponible']);

    $this->actingAs($user)->post(route('prestamos.devolver', $detalle->id_detalle), [
        'estado_equipo' => 'Danado',
        'condiciones_devolucion' => 'Regresa con parche roto.',
        'costo_reparacion' => 150,
    ])->assertRedirect(route('prestamos.activos'));

    $this->assertDatabaseHas('alertas_operativas', [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Baja y Adquisicion',
        'estado' => 'Pendiente',
    ]);
});
