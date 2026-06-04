# CulturaGest - Patrones GoF aplicados

## Diagrama de clases UML

```mermaid
classDiagram
    direction LR

    class Activo {
        +estado_actual string
        +estado_condicion string
        +estado() ActivoEstado
        +registrarPrestamo() void
    }

    class ActivoEstado {
        <<interface>>
        +nombre() string
        +registrarPrestamo(Activo) void
    }

    class DisponibleState {
        +registrarPrestamo(Activo) void
    }

    class PrestadoState {
        +registrarPrestamo(Activo) void
    }

    class EnReparacionState {
        +registrarPrestamo(Activo) void
    }

    class DanadoState {
        +registrarPrestamo(Activo) void
    }

    class ActivoEstadoFactory {
        +desdeActivo(Activo) ActivoEstado
    }

    class PrestamoController {
        +store(Request)
    }

    class AsistenteNotificacionesActivoObserver {
        +created(Activo) void
        +updated(Activo) void
    }

    class AsistenteNotificacionesMantenimientoObserver {
        +created(Mantenimiento) void
    }

    class AsistenteNotificacionesDiario {
        +activoActualizado(Activo) void
        +mantenimientoRegistrado(Mantenimiento) void
        +revisarAgendaDelDia() Collection
    }

    class AlertasOperativasService {
        <<Singleton>>
        +registrarAtencionDanio(Activo) AlertaOperativa
        +resolverAtencionDanio(Activo) int
        +registrarAgendaDiaria() Collection
        +alertasPendientes(int) Collection
    }

    class Mantenimiento {
        +id_activo int
        +fecha_servicio datetime
    }

    class AlertaOperativa {
        +tipo string
        +titulo string
        +descripcion text
        +datos json
        +estado string
    }

    Activo --> ActivoEstadoFactory : crea estado actual
    ActivoEstadoFactory ..> ActivoEstado
    ActivoEstado <|.. DisponibleState
    ActivoEstado <|.. PrestadoState
    ActivoEstado <|.. EnReparacionState
    ActivoEstado <|.. DanadoState
    PrestamoController --> Activo : registrarPrestamo()

    AsistenteNotificacionesActivoObserver ..> Activo : observa cambios
    AsistenteNotificacionesMantenimientoObserver ..> Mantenimiento : observa altas
    AsistenteNotificacionesActivoObserver --> AsistenteNotificacionesDiario
    AsistenteNotificacionesMantenimientoObserver --> AsistenteNotificacionesDiario
    AsistenteNotificacionesDiario --> AlertasOperativasService : gestor unico
    AlertasOperativasService --> AlertaOperativa : crea/resuelve
```

## Impacto arquitectonico

- **State** mueve la regla de prestamo al estado del activo. Si el activo esta en reparacion o danado, `registrarPrestamo()` bloquea la operacion automaticamente, sin duplicar condiciones en controladores.
- **Observer** desacopla el asistente de los flujos de pantalla. Cualquier cambio en `Activo` o alta de `Mantenimiento` dispara la evaluacion de alertas, aunque el cambio venga de un controlador, seeder, job o comando futuro.
- **Singleton** centraliza el control de avisos en `AlertasOperativasService`. Esto evita multiples estrategias de creacion de alertas y permite auditar en un solo lugar que mensajes se generan, actualizan o resuelven.
- La trazabilidad mejora porque cada alerta queda persistida con `tipo`, `id_activo`, `datos` y `fecha_alerta`; el personal puede reconstruir por que un bien cultural fue bloqueado, enviado a revision o propuesto para reemplazo.
- El mantenimiento preventivo mejora porque el sistema ya no depende solo de que alguien revise manualmente el inventario: el asistente registra alertas por dano/reparacion y agenda diaria de prestamos con devolucion prevista para hoy.
