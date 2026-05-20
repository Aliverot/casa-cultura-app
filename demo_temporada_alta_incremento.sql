DO $$
DECLARE
    v_user_id bigint;
    v_prev_existing integer;
    v_curr_existing integer;
    v_previous_to_insert integer := 8;
    v_current_to_insert integer;
    v_target_current integer;
    v_fecha timestamp;
    v_temporada record;
    i integer;
BEGIN
    FOR v_temporada IN
        SELECT *
        FROM (
            VALUES
                ('Ano Nuevo', '01-01', '01-01', 30),
                ('Dia de la Constitucion', '02-05', '02-05', 30),
                ('Natalicio de Benito Juarez', '03-21', '03-21', 30),
                ('Dia del Trabajo', '05-01', '05-01', 30),
                ('Independencia de Mexico', '09-16', '09-16', 30),
                ('Dia de Muertos', '11-01', '11-02', 30),
                ('Revolucion Mexicana', '11-20', '11-20', 30),
                ('Temporada decembrina', '12-12', '01-06', 30)
        ) AS t(nombre, fecha_inicio, fecha_fin, dias_anticipacion)
    LOOP
        UPDATE temporadas_base
        SET fecha_inicio = v_temporada.fecha_inicio,
            fecha_fin = v_temporada.fecha_fin,
            dias_anticipacion = v_temporada.dias_anticipacion,
            activa = TRUE,
            updated_at = NOW()
        WHERE nombre = v_temporada.nombre;

        IF NOT FOUND THEN
            INSERT INTO temporadas_base (
                nombre,
                fecha_inicio,
                fecha_fin,
                dias_anticipacion,
                activa,
                created_at,
                updated_at
            )
            VALUES (
                v_temporada.nombre,
                v_temporada.fecha_inicio,
                v_temporada.fecha_fin,
                v_temporada.dias_anticipacion,
                TRUE,
                NOW(),
                NOW()
            );
        END IF;
    END LOOP;

    DELETE FROM prestamos
    WHERE contacto_solicitante LIKE 'TEMP-ALTA-DEMO-%';

    UPDATE alertas_operativas
    SET estado = 'Resuelta',
        updated_at = NOW()
    WHERE tipo IN ('Preparacion de Temporada', 'Incremento Historico de Prestamos')
      AND estado = 'Pendiente';

    INSERT INTO users (
        identificador,
        name,
        email,
        rol,
        email_verified_at,
        password,
        created_at,
        updated_at
    )
    VALUES (
        'TEMP-ALTA-DEMO',
        'Usuario Demo Temporada Alta',
        'temporada.alta.demo@example.test',
        'Instructor',
        NOW(),
        '$2y$12$KIXRJLzFJ0YpL.G9bYfR9.CpzGpVgp8TXVjLA4NPiH1Q8n3psCQpS',
        NOW(),
        NOW()
    )
    ON CONFLICT (email) DO UPDATE
    SET updated_at = EXCLUDED.updated_at
    RETURNING id_usuario INTO v_user_id;

    INSERT INTO activos (
        codigo_qr,
        nombre,
        modelo,
        categoria,
        estado_actual,
        valor_original,
        estado_condicion,
        horas_uso,
        limite_mantenimiento,
        created_at,
        updated_at
    )
    VALUES
        ('DEMO-TEMP-001', 'Guitarra Demo Temporada', 'DEMO-TEMP-MOD', 'Instrumentos de Cuerda', 'Disponible', 3500, 'Excelente', 0, 100, NOW(), NOW()),
        ('DEMO-TEMP-002', 'Violin Demo Temporada', 'DEMO-TEMP-MOD', 'Instrumentos de Cuerda', 'Disponible', 4200, 'Excelente', 0, 100, NOW(), NOW()),
        ('DEMO-TEMP-003', 'Saxofon Demo Temporada', 'DEMO-TEMP-MOD', 'Instrumentos de Viento', 'Disponible', 8900, 'Excelente', 0, 100, NOW(), NOW()),
        ('DEMO-TEMP-004', 'Tambor Demo Temporada', 'DEMO-TEMP-MOD', 'Percusion', 'Disponible', 1800, 'Excelente', 0, 100, NOW(), NOW()),
        ('DEMO-TEMP-005', 'Teclado Demo Temporada', 'DEMO-TEMP-MOD', 'Instrumentos de Tecla', 'Disponible', 7600, 'Excelente', 0, 100, NOW(), NOW())
    ON CONFLICT (codigo_qr) DO UPDATE
    SET nombre = EXCLUDED.nombre,
        modelo = EXCLUDED.modelo,
        categoria = EXCLUDED.categoria,
        estado_actual = EXCLUDED.estado_actual,
        valor_original = EXCLUDED.valor_original,
        estado_condicion = EXCLUDED.estado_condicion,
        limite_mantenimiento = EXCLUDED.limite_mantenimiento,
        updated_at = NOW();

    SELECT COUNT(*) INTO v_prev_existing
    FROM prestamos
    WHERE fecha_salida >= NOW() - INTERVAL '60 days'
      AND fecha_salida < NOW() - INTERVAL '30 days';

    SELECT COUNT(*) INTO v_curr_existing
    FROM prestamos
    WHERE fecha_salida >= NOW() - INTERVAL '30 days';

    v_target_current := (CEIL(((v_prev_existing + v_previous_to_insert)::numeric) * 1.25) + 1)::integer;
    v_current_to_insert := GREATEST(20, v_target_current - v_curr_existing);

    FOR i IN 1..v_previous_to_insert LOOP
        v_fecha := NOW() - INTERVAL '45 days' + ((i % 10) * INTERVAL '1 day');

        INSERT INTO prestamos (
            id_usuario,
            fecha_salida,
            fecha_devolucion_prevista,
            nombre_solicitante,
            contacto_solicitante,
            condiciones_entrega,
            estado_pago,
            created_at,
            updated_at
        )
        VALUES (
            v_user_id,
            v_fecha,
            v_fecha + INTERVAL '7 days',
            'Solicitante Demo Temporada Alta',
            'TEMP-ALTA-DEMO-ANT-' || i,
            'Prestamo demo para ventana anterior',
            'Sin cargos',
            NOW(),
            NOW()
        );
    END LOOP;

    FOR i IN 1..v_current_to_insert LOOP
        v_fecha := NOW() - INTERVAL '10 days' + ((i % 7) * INTERVAL '1 day');

        INSERT INTO prestamos (
            id_usuario,
            fecha_salida,
            fecha_devolucion_prevista,
            nombre_solicitante,
            contacto_solicitante,
            condiciones_entrega,
            estado_pago,
            created_at,
            updated_at
        )
        VALUES (
            v_user_id,
            v_fecha,
            v_fecha + INTERVAL '7 days',
            'Solicitante Demo Temporada Alta',
            'TEMP-ALTA-DEMO-ACT-' || i,
            'Prestamo demo para ventana reciente',
            'Sin cargos',
            NOW(),
            NOW()
        );
    END LOOP;

    INSERT INTO detalle_prestamos (
        id_prestamo,
        id_activo,
        estado_salida,
        estado_retorno,
        fecha_devolucion_real,
        created_at,
        updated_at
    )
    WITH demo_prestamos AS (
        SELECT p.id_prestamo,
               p.fecha_salida,
               ROW_NUMBER() OVER (ORDER BY p.fecha_salida, p.id_prestamo) AS orden
        FROM prestamos p
        WHERE p.contacto_solicitante LIKE 'TEMP-ALTA-DEMO-%'
    ),
    prestamos_asignados AS (
        SELECT id_prestamo,
               fecha_salida,
               CASE
                   WHEN orden % 15 IN (1, 2, 3, 4, 5) THEN 'DEMO-TEMP-005'
                   WHEN orden % 15 IN (6, 7, 8, 9) THEN 'DEMO-TEMP-001'
                   WHEN orden % 15 IN (10, 11, 12) THEN 'DEMO-TEMP-003'
                   WHEN orden % 15 IN (13, 14) THEN 'DEMO-TEMP-002'
                   ELSE 'DEMO-TEMP-004'
               END AS codigo_qr
        FROM demo_prestamos
    )
    SELECT pa.id_prestamo,
           a.id_activo,
           'Prestado',
           'Buen estado',
           pa.fecha_salida + INTERVAL '1 day',
           NOW(),
           NOW()
    FROM prestamos_asignados pa
    JOIN activos a ON a.codigo_qr = pa.codigo_qr;

    RAISE NOTICE 'Demo listo. Fechas base precargadas: 8. Prestamos anteriores insertados: %, prestamos recientes insertados: %.',
        v_previous_to_insert,
        v_current_to_insert;
END $$;
