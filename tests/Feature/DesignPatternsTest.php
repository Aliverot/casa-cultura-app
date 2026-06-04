<?php

use App\Models\Activo;
use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use App\Models\User;
use App\Services\AsistenteNotificacionesDiario;
use App\Services\AlertasOperativasService;
use App\States\Activo\EnReparacionState;
use Carbon\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

it('uses a singleton alert manager for the notification assistant', function () {
    $primerGestor = app(AlertasOperativasService::class);
    $segundoGestor = app(AlertasOperativasService::class);

    expect($primerGestor)->toBe($segundoGestor);
});

it('blocks loans automatically when the asset state is under repair', function () {
    Carbon::setTestNow('2026-06-03 09:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-STATE-001',
        'nombre' => 'Marimba en reparación',
        'categoria' => 'Percusiones',
        'estado_actual' => Activo::ESTADO_MANTENIMIENTO,
        'estado_condicion' => Activo::ESTADO_CONDICION_EN_REPARACION,
        'horas_uso' => 12,
        'limite_mantenimiento' => 20,
    ]);

    expect($activo->estado())->toBeInstanceOf(EnReparacionState::class);

    $this->actingAs($user)->from(route('prestamos.create'))->post(route('prestamos.store'), [
        'id_activo' => $activo->id_activo,
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => '9517654321',
        'condiciones_entrega' => 'No debe salir.',
        'fecha_devolucion_prevista' => '2026-06-03T12:00',
    ])->assertSessionHasErrors('id_activo');
});

it('fires the daily assistant observer when an asset becomes damaged', function () {
    $activo = Activo::create([
        'codigo_qr' => 'QR-OBS-001',
        'nombre' => 'Guitarra con golpe',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => Activo::ESTADO_DISPONIBLE,
        'estado_condicion' => 'Excelente',
        'horas_uso' => 0,
        'limite_mantenimiento' => 20,
    ]);

    $activo->estado_actual = Activo::ESTADO_MANTENIMIENTO;
    $activo->estado_condicion = Activo::ESTADO_DANADO;
    $activo->save();

    $this->assertDatabaseHas('alertas_operativas', [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Atencion a Dano',
        'titulo' => 'Tienes objetos en estado dañado que requieren atención',
        'estado' => 'Pendiente',
    ]);
});

it('creates the daily pickup and delivery agenda alert from the assistant', function () {
    Carbon::setTestNow('2026-06-03 08:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-AGENDA-001',
        'nombre' => 'Vestuario agenda',
        'categoria' => 'Vestuario',
        'estado_actual' => Activo::ESTADO_DISPONIBLE,
        'estado_condicion' => 'Excelente',
        'horas_uso' => 0,
        'limite_mantenimiento' => 20,
    ]);

    $prestamo = Prestamo::create([
        'id_usuario' => $user->id_usuario,
        'fecha_salida' => now()->subHour(),
        'fecha_devolucion_prevista' => now()->addHours(4),
        'nombre_solicitante' => 'Danza Infantil',
        'contacto_solicitante' => 'GRUPO-01',
        'condiciones_entrega' => 'Completo',
        'estado_pago' => 'Sin cargos',
    ]);

    DetallePrestamo::create([
        'id_prestamo' => $prestamo->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => Activo::ESTADO_PRESTADO,
    ]);

    app(AsistenteNotificacionesDiario::class)->revisarAgendaDelDia();

    $this->assertDatabaseHas('alertas_operativas', [
        'id_activo' => null,
        'tipo' => 'Agenda Diaria de Prestamos',
        'titulo' => 'Hoy se deben recoger/entregar estos materiales',
        'estado' => 'Pendiente',
    ]);
});
