<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/logger.php';

// Configuración inicial
date_default_timezone_set('America/Mexico_City');

/**
 * Función para mostrar recordatorios según la hora actual
 * @return array Lista de recordatorios activos que coinciden con la hora actual o están próximos
 */
function mostrarRecordatorios() {
    $conexion = conectarDB();
    $stmt = null;
    
    try {
        Logger::info("Inicio de consulta de recordatorios generales");

        // Configurar zona horaria para MySQL
        $offset = (new DateTime())->format('P');
        $conexion->query("SET time_zone = '$offset'");

        // Obtener hora actual en formato HH:MM
        $hora_actual = (new DateTime())->format('H:i');
        Logger::debug("Hora actual del sistema", ['hora_actual' => $hora_actual]);
        
        // Consulta modificada para obtener todos los recordatorios activos
        $sql = "SELECT r.id_recordatorio, r.dispositivo_id, r.hora, r.mensaje, 
                       d.nombre as nombre_dispositivo
                FROM recordatorios r
                JOIN dispositivos d ON r.dispositivo_id = d.id_dispositivo
                WHERE r.activo = 1
                AND r.hora <= ?
                ORDER BY r.hora DESC";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            Logger::error('Error al preparar consulta de recordatorios', [
                'error' => $conexion->error,
                'sql' => $sql
            ]);
            throw new Exception('Error al preparar la consulta: ' . $conexion->error);
        }

        $stmt->bind_param("s", $hora_actual);
        
        if (!$stmt->execute()) {
            Logger::error('Error al ejecutar consulta de recordatorios', [
                'error' => $stmt->error
            ]);
            throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $recordatorios = [];
        $total_coinciden = 0;
        
        while ($row = $result->fetch_assoc()) {
            $estado = ($row['hora'] == $hora_actual) ? 'coincide' : 'pendiente';
            if ($estado == 'coincide') $total_coinciden++;
            
            $recordatorios[] = [
                'id' => $row['id_recordatorio'],
                'dispositivo_id' => $row['dispositivo_id'],
                'dispositivo' => $row['nombre_dispositivo'],
                'hora' => $row['hora'],
                'mensaje' => $row['mensaje'],
                'hora_actual' => $hora_actual,
                'estado' => $estado
            ];
        }

        Logger::info("Consulta de recordatorios exitosa", [
            'total_recordatorios' => count($recordatorios),
            'total_coinciden' => $total_coinciden
        ]);

        return [
            'success' => true,
            'hora_actual' => $hora_actual,
            'recordatorios' => $recordatorios,
            'total' => count($recordatorios),
            'coinciden' => $total_coinciden
        ];

    } catch (Exception $e) {
        Logger::error("Error en mostrarRecordatorios", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    } finally {
        if ($stmt !== null && is_object($stmt)) $stmt->close();
        if ($conexion !== null) $conexion->close();
    }
}

// Manejo de la solicitud
try {
    Logger::debug("Solicitud de recordatorios generales recibida", [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'desconocido',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
    ]);

    $resultado = mostrarRecordatorios();
    
    // Para API JSON
    header('Content-Type: application/json');
    echo json_encode($resultado);
    
    Logger::debug("Respuesta enviada al cliente", [
        'total_recordatorios' => $resultado['total']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    Logger::error("Error en la solicitud de recordatorios", [
        'error' => $e->getMessage(),
        'response' => $response
    ]);
    
    echo json_encode($response);
}