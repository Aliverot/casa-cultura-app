<?php

use App\Models\Activo;
use App\Models\AlertaOperativa;
use App\Models\DetallePrestamo;
use App\Models\Prestamo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

it('shows return incident details for instruments in maintenance', function () {
    Carbon::setTestNow('2026-04-27 16:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100018',
        'nombre' => 'Flauta con golpe',
        'categoria' => 'Instrumentos de Viento',
        'estado_actual' => 'Mantenimiento',
        'estado_condicion' => 'En reparacion',
        'horas_uso' => 7,
        'limite_mantenimiento' => 20,
    ]);

    $prestamo = Prestamo::create([
        'id_usuario' => $user->id_usuario,
        'fecha_salida' => now()->subHours(4),
        'fecha_devolucion_prevista' => now()->subHour(),
        'nombre_solicitante' => 'Alumno Demo',
        'contacto_solicitante' => 'MAT-018',
        'condiciones_entrega' => 'Salio afinada y sin golpes',
        'condiciones_devolucion' => 'Regresa con abolladura en la boquilla.',
        'costo_reparacion' => 200,
        'estado_pago' => 'Pendiente',
    ]);

    DetallePrestamo::create([
        'id_prestamo' => $prestamo->id_prestamo,
        'id_activo' => $activo->id_activo,
        'estado_salida' => 'Prestado',
        'estado_retorno' => 'Danado',
        'contexto_incidente' => 'Se cayo durante el traslado al salon.',
        'entorno_uso' => 'Traslado interno',
        'accesorios_proteccion' => 'Sin estuche rigido',
        'fecha_devolucion_real' => now(),
    ]);

    $this->actingAs($user)->get(route('mantenimientos.index'))
        ->assertOk()
        ->assertSee('Motivo de reparacion')
        ->assertSee('Regresa con abolladura en la boquilla.')
        ->assertSee('Se cayo durante el traslado al salon.')
        ->assertSee('Traslado interno')
        ->assertSee('Sin estuche rigido');
});

it('creates historical demand alerts when recent demand increases', function () {
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
        'tipo' => 'Incremento Historico de Prestamos',
        'estado' => 'Pendiente',
    ]);

    $alerta = AlertaOperativa::where('tipo', 'Incremento Historico de Prestamos')
        ->where('estado', 'Pendiente')
        ->first();

    expect($alerta->datos['prestamos_anteriores'])->toBe(2)
        ->and($alerta->datos['prestamos_actuales'])->toBe(3)
        ->and($alerta->datos['incremento_porcentaje'])->toBe(50)
        ->and($alerta->datos['umbral_incremento'])->toBe(25)
        ->and($alerta->datos['minimo_prestamos_anteriores'])->toBe(2)
        ->and($alerta->datos['minimo_prestamos_actuales'])->toBe(3)
        ->and($alerta->datos['aplica_por_incremento'])->toBeTrue();
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

it('shows base season and historical demand as independent alerts', function () {
    Carbon::setTestNow('2026-08-20 10:00:00');

    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100015',
        'nombre' => 'Flauta de prueba',
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
            'contacto_solicitante' => 'BASE-HIST-'.$dias,
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
            'contacto_solicitante' => 'BASE-HIST-'.$dias,
            'condiciones_entrega' => 'En buen estado',
            'estado_pago' => 'Sin cargos',
        ]);

        DetallePrestamo::create([
            'id_prestamo' => $prestamo->id_prestamo,
            'id_activo' => $activo->id_activo,
            'estado_salida' => 'Prestado',
        ]);
    }

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $this->assertDatabaseHas('alertas_operativas', [
        'tipo' => 'Preparacion de Temporada',
        'estado' => 'Pendiente',
    ]);

    $this->assertDatabaseHas('alertas_operativas', [
        'tipo' => 'Incremento Historico de Prestamos',
        'estado' => 'Pendiente',
    ]);
});

it('preloads mexican base seasons without showing the test button in the calendar screen', function () {
    $user = User::factory()->create();

    DB::table('temporadas_base')->delete();

    $this->actingAs($user)->get(route('temporadas-base.index'))
        ->assertOk()
        ->assertSee('Agregar Temporada')
        ->assertDontSee('Cargar fechas mexicanas');

    $this->actingAs($user)->post(route('temporadas-base.precargar'))
        ->assertRedirect(route('temporadas-base.index'))
        ->assertSessionHas('success');

    expect(DB::table('temporadas_base')->count())->toBe(8);

    $this->assertDatabaseHas('temporadas_base', [
        'nombre' => 'Independencia de Mexico',
        'fecha_inicio' => '09-16',
        'fecha_fin' => '09-16',
        'dias_anticipacion' => 30,
        'activa' => true,
    ]);

    $this->assertDatabaseHas('temporadas_base', [
        'nombre' => 'Temporada decembrina',
        'fecha_inicio' => '12-12',
        'fecha_fin' => '01-06',
        'dias_anticipacion' => 30,
        'activa' => true,
    ]);
});

it('adds custom base seasons from the calendar screen', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('temporadas-base.store'), [
        'nombre' => 'Festival Comunitario',
        'fecha_inicio' => '06-10',
        'fecha_fin' => '06-15',
        'dias_anticipacion' => 20,
        'activa' => 1,
    ])->assertRedirect(route('temporadas-base.index'));

    $this->assertDatabaseHas('temporadas_base', [
        'nombre' => 'Festival Comunitario',
        'fecha_inicio' => '06-10',
        'fecha_fin' => '06-15',
        'dias_anticipacion' => 20,
        'activa' => true,
    ]);

    $this->actingAs($user)->get(route('temporadas-base.index'))
        ->assertOk()
        ->assertSee('Festival Comunitario');
});

it('updates custom base seasons from the calendar screen', function () {
    $user = User::factory()->create();

    $id = DB::table('temporadas_base')->insertGetId([
        'nombre' => 'Festival Comunitario',
        'fecha_inicio' => '06-10',
        'fecha_fin' => '06-15',
        'dias_anticipacion' => 20,
        'activa' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)->patch(route('temporadas-base.update', $id), [
        'nombre' => 'Festival Comunitario Ajustado',
        'fecha_inicio' => '06-11',
        'fecha_fin' => '06-16',
        'dias_anticipacion' => 25,
    ])->assertRedirect(route('temporadas-base.index'));

    $this->assertDatabaseHas('temporadas_base', [
        'id_temporada' => $id,
        'nombre' => 'Festival Comunitario Ajustado',
        'fecha_inicio' => '06-11',
        'fecha_fin' => '06-16',
        'dias_anticipacion' => 25,
        'activa' => false,
    ]);
});

it('shows preventive maintenance in history', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100016',
        'nombre' => 'Guitarra preventiva',
        'categoria' => 'Instrumentos de Cuerda',
        'estado_actual' => 'Mantenimiento',
        'estado_condicion' => 'En reparacion',
        'horas_uso' => 40,
        'limite_mantenimiento' => 20,
    ]);

    $this->actingAs($user)->post(route('mantenimientos.store'), [
        'id_activo' => $activo->id_activo,
        'tipo' => 'Revision preventiva',
        'estado_condicion' => 'Excelente',
        'es_preventivo' => 1,
    ])->assertRedirect(route('mantenimientos.index'));

    $this->actingAs($user)->get(route('mantenimientos.index'))
        ->assertOk()
        ->assertSee('Revision preventiva')
        ->assertSee('Preventivo');
});

it('shows pending replacement reports in maintenance', function () {
    $user = User::factory()->create();
    $activo = Activo::create([
        'codigo_qr' => 'QR-100017',
        'nombre' => 'Trompeta reemplazo',
        'modelo' => 'TR-2026',
        'categoria' => 'Instrumentos de Viento',
        'estado_actual' => 'Disponible',
        'valor_original' => 1000,
        'horas_uso' => 0,
        'limite_mantenimiento' => 20,
    ]);

    AlertaOperativa::create([
        'id_activo' => $activo->id_activo,
        'tipo' => 'Baja y Adquisicion',
        'titulo' => 'Informe de Baja y Adquisicion',
        'descripcion' => 'Se recomienda evaluar la compra de nuevas unidades de TR-2026.',
        'estado' => 'Pendiente',
        'datos' => [
            'referencia_modelo' => 'TR-2026',
            'fallas' => 3,
            'costo_reparaciones' => 750,
            'costo_mantenimientos_preventivos' => 150,
            'mantenimientos_preventivos' => 1,
            'valor_original' => 1000,
            'periodo_dias' => 365,
        ],
    ]);

    $this->actingAs($user)->get(route('mantenimientos.index'))
        ->assertOk()
        ->assertSee('Informe de Baja y Adquisicion')
        ->assertSee('TR-2026')
        ->assertSee('75.0%')
        ->assertSee('Preventivo: $150.00');
});
