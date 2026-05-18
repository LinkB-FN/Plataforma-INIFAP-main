<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Renombrar las columnas para que coincidan con las que espera Laravel por defecto
    DB::unprepared('
        ALTER TABLE sesiones RENAME COLUMN id_usuario TO user_id;
        ALTER TABLE sesiones RENAME COLUMN ultimo_actividad TO last_activity;

        CREATE OR REPLACE FUNCTION fn_limpiar_sesiones()
        RETURNS INT AS $$
        DECLARE
            v_eliminadas INT;
        BEGIN
            DELETE FROM sesiones
            WHERE last_activity < EXTRACT(EPOCH FROM NOW() - INTERVAL \'120 minutes\')::BIGINT;

            GET DIAGNOSTICS v_eliminadas = ROW_COUNT;

            INSERT INTO bitacora (accion, detalle, fecha)
            VALUES (\'LIMPIEZA_SESIONES\',
                    jsonb_build_object(\'sesiones_eliminadas\', v_eliminadas),
                    NOW());

            RETURN v_eliminadas;
        END;
        $$ LANGUAGE plpgsql;
    ');
    
    echo "Columnas renombradas exitosamente.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
