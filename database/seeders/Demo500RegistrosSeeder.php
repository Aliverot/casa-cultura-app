<?php

namespace Database\Seeders;

use App\Models\Activo;
use App\Models\AlertaOperativa;
use App\Models\DetallePrestamo;
use App\Models\Mantenimiento;
use App\Models\Prestamo;
use App\Models\User;
use App\Services\AlertasOperativasService;
use App\Services\AsistenteNotificacionesDiario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Demo500RegistrosSeeder extends Seeder
{
    private const OLD_PREFIX = 'DEMO500';
    private const QR_PREFIX = 'CCG-INV-26';

    public function run(): void
    {
        $user = User::orderBy('id_usuario')->first();

        if (! $user) {
            throw new RuntimeException('Necesitas al menos un usuario existente antes de cargar los registros de prueba.');
        }

        DB::transaction(function () use ($user) {
            $this->limpiarDemoAnterior();

            $activos = $this->crearActivos();
            $this->crearPrestamosActivos($user, $activos->where('estado_actual', Activo::ESTADO_PRESTADO_LEGACY)->values());
            $this->crearHistorialPrestamos($user, $activos);
            $this->crearMantenimientos($activos);
            $this->crearTemporadasBase();

            $this->activarAlertasDelSistema($activos);
        });

        $conteo = $this->conteoRegistrosBase();
        $total = array_sum($conteo);
        $alertas = AlertaOperativa::where(function ($query) {
            $query->whereHas('activo', fn ($activoQuery) => $activoQuery->where('codigo_qr', 'like', self::QR_PREFIX.'-%'))
                ->orWhereIn('tipo', [
                    'Agenda Diaria de Prestamos',
                    'Preparacion de Temporada',
                    'Incremento Historico de Prestamos',
                ]);
        })->where('estado', 'Pendiente')->count();

        if ($total !== 500) {
            throw new RuntimeException('La carga de prueba esperaba 500 registros base y generó '.$total.'.');
        }

        $this->command?->info('Carga de prueba lista: '.json_encode($conteo, JSON_UNESCAPED_UNICODE).' Total='.$total.'. Alertas pendientes visibles: '.$alertas.'.');
    }

    private function limpiarDemoAnterior(): void
    {
        $idsActivos = Activo::where(function ($query) {
            $query->where('codigo_qr', 'like', self::QR_PREFIX.'-%')
                ->orWhere('codigo_qr', 'like', self::OLD_PREFIX.'-%');
        })->pluck('id_activo');

        $idsPrestamos = DetallePrestamo::whereIn('id_activo', $idsActivos)
            ->pluck('id_prestamo')
            ->merge(Prestamo::where('contacto_solicitante', 'like', self::OLD_PREFIX.'-%')->pluck('id_prestamo'))
            ->unique()
            ->values();

        AlertaOperativa::whereIn('id_activo', $idsActivos)->delete();
        AlertaOperativa::whereNull('id_activo')
            ->whereIn('tipo', [
                'Agenda Diaria de Prestamos',
                'Preparacion de Temporada',
                'Incremento Historico de Prestamos',
            ])
            ->get()
            ->filter(function (AlertaOperativa $alerta) {
                $contenido = $alerta->descripcion.' '.json_encode($alerta->datos, JSON_UNESCAPED_UNICODE);

                return str_contains($contenido, self::OLD_PREFIX)
                    || str_contains($contenido, self::QR_PREFIX);
            })
            ->each->delete();
        Mantenimiento::whereIn('id_activo', $idsActivos)->delete();
        DetallePrestamo::whereIn('id_prestamo', $idsPrestamos)
            ->orWhereIn('id_activo', $idsActivos)
            ->delete();
        Prestamo::whereIn('id_prestamo', $idsPrestamos)->delete();
        Activo::whereIn('id_activo', $idsActivos)->delete();

        DB::table('temporadas_base')
            ->where('nombre', 'like', self::OLD_PREFIX.' %')
            ->orWhereIn('nombre', $this->nombresTemporadasPrueba())
            ->delete();
        DB::table('users')->where('email', 'like', 'demo500.%@example.test')->delete();
    }

    private function crearActivos(): Collection
    {
        $activos = collect();

        for ($i = 1; $i <= 170; $i++) {
            $categoria = match (($i - 1) % 5) {
                0 => 'Instrumentos de Cuerda',
                1 => 'Instrumentos de Viento',
                2 => 'Percusiones',
                3 => 'Vestuario/Danza',
                default => 'Equipo de Sonido',
            };

            $estadoActual = match (true) {
                $i <= 100 => Activo::ESTADO_DISPONIBLE,
                $i <= 120 => Activo::ESTADO_PRESTADO_LEGACY,
                $i <= 145 => Activo::ESTADO_MANTENIMIENTO,
                $i <= 155 => Activo::ESTADO_EXTRAVIADO,
                default => Activo::ESTADO_BAJA,
            };

            $estadoCondicion = match ($estadoActual) {
                Activo::ESTADO_DISPONIBLE => $i % 5 === 0 ? 'Funcional con detalles' : 'Excelente',
                Activo::ESTADO_PRESTADO_LEGACY => $i % 4 === 0 ? 'Funcional con detalles' : 'Excelente',
                Activo::ESTADO_MANTENIMIENTO => $i % 3 === 0 ? Activo::ESTADO_DANADO : Activo::ESTADO_CONDICION_EN_REPARACION,
                Activo::ESTADO_EXTRAVIADO => $i % 2 === 0 ? 'Funcional con detalles' : 'Excelente',
                default => 'Baja definitiva',
            };

            $limiteMantenimiento = 40 + (($i % 7) * 10);
            $horasUso = $i <= 12 ? $limiteMantenimiento + 18 : round($i * 1.65, 2);

            $activos->push(Activo::create([
                'codigo_qr' => self::QR_PREFIX.'-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'nombre' => $this->nombreActivo($categoria, $i),
                'modelo' => $this->modeloActivo($categoria, $i),
                'categoria' => $categoria,
                'estado_actual' => $estadoActual,
                'valor_original' => 900 + (($i % 40) * 240),
                'estado_condicion' => $estadoCondicion,
                'horas_uso' => $horasUso,
                'limite_mantenimiento' => $limiteMantenimiento,
            ]));
        }

        return $activos;
    }

    private function crearPrestamosActivos(User $user, Collection $activosPrestados): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $activo = $activosPrestados[$i - 1];
            $fechaSalida = now()->subDays($i % 6)->subHours(2);

            $prestamo = Prestamo::create([
                'id_usuario' => $user->id_usuario,
                'fecha_salida' => $fechaSalida,
                'fecha_devolucion_prevista' => $i <= 8
                    ? now()->setTime(18, 0)
                    : ($i <= 14 ? now()->addDay()->setTime(12, 0) : now()->subHours(3)),
                'nombre_solicitante' => $this->nombreSolicitante($i, true),
                'contacto_solicitante' => $this->telefonoSolicitante($i),
                'condiciones_entrega' => 'Préstamo activo con salida registrada y pendiente de devolución.',
                'estado_pago' => 'Sin cargos',
            ]);

            DetallePrestamo::create([
                'id_prestamo' => $prestamo->id_prestamo,
                'id_activo' => $activo->id_activo,
                'estado_salida' => Activo::ESTADO_PRESTADO,
            ]);
        }
    }

    private function crearHistorialPrestamos(User $user, Collection $activos): void
    {
        $disponibles = $activos->where('estado_actual', Activo::ESTADO_DISPONIBLE)->values();
        $mantenimiento = $activos->where('estado_actual', Activo::ESTADO_MANTENIMIENTO)->values();
        $extraviados = $activos->where('estado_actual', Activo::ESTADO_EXTRAVIADO)->values();
        $baja = $activos->where('estado_actual', Activo::ESTADO_BAJA)->values();

        for ($i = 1; $i <= 140; $i++) {
            $fechaSalida = match (true) {
                $i <= 110 => now()->subDays($i % 26)->subHours($i % 8),
                $i <= 130 => now()->subDays(38 + ($i % 18)),
                default => now()->subDays(110 + ($i % 60)),
            };

            [$activo, $estadoRetorno] = $this->activoYRetornoParaHistorial($i, $disponibles, $mantenimiento, $extraviados, $baja);
            $costo = $estadoRetorno === 'Danado'
                ? 150 + (($i % 8) * 90)
                : ($estadoRetorno === 'Perdida total' ? 1200 + (($i % 7) * 180) : 0);

            $prestamo = Prestamo::create([
                'id_usuario' => $user->id_usuario,
                'fecha_salida' => $fechaSalida,
                'fecha_devolucion_prevista' => $fechaSalida->copy()->addDays(5),
                'nombre_solicitante' => $this->nombreSolicitante($i + 20),
                'contacto_solicitante' => $this->telefonoSolicitante($i + 20),
                'condiciones_entrega' => 'Salida documentada para pruebas de historial y auditoría.',
                'condiciones_devolucion' => $this->condicionesDevolucion($estadoRetorno),
                'costo_reparacion' => $costo,
                'estado_pago' => $costo > 0 ? ($i % 2 === 0 ? 'Pendiente' : 'Pagado') : 'Sin cargos',
            ]);

            DetallePrestamo::create([
                'id_prestamo' => $prestamo->id_prestamo,
                'id_activo' => $activo->id_activo,
                'estado_salida' => Activo::ESTADO_PRESTADO,
                'estado_retorno' => $estadoRetorno,
                'contexto_incidente' => $estadoRetorno === 'Danado' ? 'Daño detectado durante transporte, ensayo o evento exterior.' : null,
                'entorno_uso' => $estadoRetorno === 'Danado' ? (['Ensayo', 'Evento exterior', 'Transporte'][$i % 3]) : null,
                'accesorios_proteccion' => $estadoRetorno === 'Danado' ? 'Funda, estuche o protección documentada al retorno.' : null,
                'fecha_devolucion_real' => $fechaSalida->copy()->addDays(2)->addHours($i % 5),
            ]);
        }
    }

    private function crearMantenimientos(Collection $activos): void
    {
        $activosMantenimiento = $activos->where('estado_actual', Activo::ESTADO_MANTENIMIENTO)->values();
        $activosDisponibles = $activos->where('estado_actual', Activo::ESTADO_DISPONIBLE)->values();

        for ($i = 1; $i <= 8; $i++) {
            $activo = $i <= 3 ? $activosMantenimiento[$i - 1] : $activosDisponibles[$i + 5];

            Mantenimiento::create([
                'id_activo' => $activo->id_activo,
                'fecha_servicio' => now()->subDays($i * 7),
                'tipo' => match ($i % 5) {
                    0 => 'Limpieza profunda',
                    1 => 'Afinación general',
                    2 => 'Revisión eléctrica',
                    3 => 'Cambio de cuerdas',
                    default => 'Ajuste de puente',
                },
                'costo_servicio' => 280 + ($i * 110),
                'es_preventivo' => $i % 3 === 0,
                'observaciones' => $this->observacionMantenimiento($i),
            ]);
        }
    }

    private function crearTemporadasBase(): void
    {
        DB::table('temporadas_base')->insert([
            [
                'nombre' => 'Clausura de talleres culturales',
                'fecha_inicio' => '06-20',
                'fecha_fin' => '06-30',
                'dias_anticipacion' => 30,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Festival infantil comunitario',
                'fecha_inicio' => '04-30',
                'fecha_fin' => '04-30',
                'dias_anticipacion' => 20,
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function activarAlertasDelSistema(Collection $activos): void
    {
        $alertas = app(AlertasOperativasService::class);
        $asistente = app(AsistenteNotificacionesDiario::class);

        $activos->where('estado_actual', Activo::ESTADO_MANTENIMIENTO)
            ->take(8)
            ->each(function (Activo $activo) use ($alertas) {
                $alertas->registrarAtencionDanio($activo);
                $alertas->registrarDanioRecurrente($activo);
                $alertas->registrarReposicionSiAplica($activo);
            });

        $asistente->revisarAgendaDelDia();
        $alertas->registrarTemporadaSiAplica();
    }

    private function activoYRetornoParaHistorial(int $i, Collection $disponibles, Collection $mantenimiento, Collection $extraviados, Collection $baja): array
    {
        if ($i <= 9) {
            return [$mantenimiento[0], 'Danado'];
        }

        if ($i <= 16) {
            return [$mantenimiento[1], 'Danado'];
        }

        if ($i <= 22) {
            return [$mantenimiento[2], 'Danado'];
        }

        if ($i % 23 === 0) {
            return [$baja[($i / 23) % $baja->count()], 'Perdida total'];
        }

        if ($i % 17 === 0) {
            return [$extraviados[($i / 17) % $extraviados->count()], 'Extraviado'];
        }

        if ($i % 5 === 0) {
            return [$mantenimiento[($i % $mantenimiento->count())], 'Danado'];
        }

        return [$disponibles[($i - 1) % $disponibles->count()], $i % 4 === 0 ? 'Con atraso' : 'En tiempo y forma'];
    }

    private function condicionesDevolucion(string $estadoRetorno): string
    {
        return match ($estadoRetorno) {
            'Danado' => 'Devolución con daño visible; se manda a evaluación de mantenimiento.',
            'Extraviado' => 'El material no fue localizado al cierre del préstamo.',
            'Perdida total' => 'El material se reporta como pérdida total documentada.',
            'Con atraso' => 'Devolución en buen estado, pero fuera del horario previsto.',
            default => 'Devolución en buen estado y dentro del flujo normal.',
        };
    }

    private function nombreActivo(string $categoria, int $i): string
    {
        $nombre = match ($categoria) {
            'Instrumentos de Cuerda' => ['Guitarra clásica', 'Violín de taller', 'Jarana tradicional', 'Bajo acústico'][$i % 4],
            'Instrumentos de Viento' => ['Clarinete escolar', 'Saxofón alto', 'Flauta transversal', 'Trompeta de banda'][$i % 4],
            'Percusiones' => ['Tambor ceremonial', 'Tarola de banda', 'Bombo de comparsa', 'Marimba portátil'][$i % 4],
            'Vestuario/Danza' => ['Huipil bordado', 'Falda regional', 'Sombrero de danza', 'Traje de jarabe'][$i % 4],
            default => ['Micrófono vocal', 'Bocina amplificada', 'Consola de audio', 'Pedestal de escenario'][$i % 4],
        };

        return $nombre.' No. '.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
    }

    private function modeloActivo(string $categoria, int $i): string
    {
        return match ($categoria) {
            'Instrumentos de Cuerda' => ['Yamaha C40', 'La Valenciana LV-12', 'Cremona SV-175', 'Lucida LG-510'][$i % 4],
            'Instrumentos de Viento' => ['Yamaha YCL-255', 'Jupiter JAS-500', 'Gemeinhardt 2SP', 'Bach TR300'][$i % 4],
            'Percusiones' => ['Pearl Roadshow', 'Ludwig Accent', 'Mapex Tornado', 'Adams MSPV43'][$i % 4],
            'Vestuario/Danza' => ['Conjunto Cuilápam azul', 'Traje regional Oaxaca', 'Vestuario jarabe clásico', 'Huipil municipal'][$i % 4],
            default => ['Shure SM58', 'Behringer B112D', 'Yamaha MG10XU', 'On-Stage MS7701'][$i % 4],
        };
    }

    private function nombreSolicitante(int $i, bool $grupoActivo = false): string
    {
        $grupos = [
            'Taller de guitarra vespertino',
            'Banda juvenil municipal',
            'Grupo de danza tradicional',
            'Coro comunitario',
            'Taller infantil de música',
            'Comité de fiestas de barrio',
            'Ensayo de teatro comunitario',
            'Clase de son jarocho',
            'Comparsa de carnaval',
            'Taller de marimba',
        ];

        $personas = [
            'María Fernanda López',
            'José Antonio Cruz',
            'Ana Paola Martínez',
            'Luis Enrique Santiago',
            'Rosa Elena García',
            'Miguel Ángel Hernández',
            'Daniela Robles Méndez',
            'Carlos Alberto Jiménez',
            'Sofía Itzel Ramírez',
            'Juan Pablo Aquino',
            'Elena Patricia Morales',
            'Pedro Nicolás Reyes',
            'Claudia Beatriz Vásquez',
            'Hugo Iván Mendoza',
            'Teresa del Carmen Ruiz',
        ];

        if ($grupoActivo || $i % 3 === 0) {
            return $grupos[$i % count($grupos)];
        }

        return $personas[$i % count($personas)];
    }

    private function telefonoSolicitante(int $i): string
    {
        return '951 '.str_pad((string) (120 + ($i % 780)), 3, '0', STR_PAD_LEFT).' '.str_pad((string) (($i * 37) % 10000), 4, '0', STR_PAD_LEFT);
    }

    private function observacionMantenimiento(int $i): string
    {
        return [
            'Revisión general después de uso continuo en talleres.',
            'Ajuste preventivo solicitado por coordinación de actividades.',
            'Se detectó desgaste durante inventario y se programó servicio.',
            'Limpieza y calibración para próximo evento comunitario.',
            'Seguimiento por reporte de uso intensivo en temporada alta.',
            'Mantenimiento correctivo con registro de costo acumulado.',
            'Revisión por sonido irregular reportado en ensayo.',
            'Servicio preventivo antes de préstamo externo.',
        ][$i - 1];
    }

    private function nombresTemporadasPrueba(): array
    {
        return [
            'Clausura de talleres culturales',
            'Festival infantil comunitario',
        ];
    }

    private function conteoRegistrosBase(): array
    {
        $idsActivos = Activo::where('codigo_qr', 'like', self::QR_PREFIX.'-%')->pluck('id_activo');
        $idsPrestamos = DetallePrestamo::whereIn('id_activo', $idsActivos)
            ->pluck('id_prestamo')
            ->unique()
            ->values();

        return [
            'activos' => $idsActivos->count(),
            'prestamos' => $idsPrestamos->count(),
            'detalle_prestamos' => DetallePrestamo::whereIn('id_prestamo', $idsPrestamos)->count(),
            'mantenimientos' => Mantenimiento::whereIn('id_activo', $idsActivos)->count(),
            'temporadas_base' => DB::table('temporadas_base')->whereIn('nombre', $this->nombresTemporadasPrueba())->count(),
        ];
    }
}
