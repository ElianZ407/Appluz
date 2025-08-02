<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/logger.php'; // Asegúrate de tener tu Logger

// Configuración inicial
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function agregarDispositivo($usuario_id, $nombre, $id_tipo_dispositivo, $ubicacion = null, $esta_encendido = false) {
    // Configuración de zona horaria
    date_default_timezone_set('America/Mexico_City');
    
    Logger::info("Inicio de registro de dispositivo", [
        'user_id' => $usuario_id,
        'device_name' => $nombre,
        'type_id' => $id_tipo_dispositivo
    ]);

    // Validaciones básicas
    if (empty($usuario_id)) {
        Logger::error("Intento de registro sin usuario_id");
        throw new Exception('ID de usuario requerido');
    }

    if (empty($nombre)) {
        Logger::error("Intento de registro sin nombre de dispositivo");
        throw new Exception('El nombre del dispositivo es requerido');
    }

    if (empty($id_tipo_dispositivo)) {
        Logger::error("Intento de registro sin tipo de dispositivo");
        throw new Exception('El tipo de dispositivo es requerido');
    }

    $conexion = conectarDB();
    
    try {
        Logger::debug("Verificando tipo de dispositivo", ['type_id' => $id_tipo_dispositivo]);
        
        // 1. Verificar que el tipo de dispositivo existe
        $sql_check = "SELECT id_tipo_dispositivo FROM tipo_dispositivo WHERE id_tipo_dispositivo = ?";
        $stmt_check = $conexion->prepare($sql_check);
        
        if (!$stmt_check) {
            Logger::error("Error al preparar consulta de verificación", [
                'error' => $conexion->error,
                'type_id' => $id_tipo_dispositivo
            ]);
            throw new Exception('Error al verificar tipo de dispositivo: ' . $conexion->error);
        }

        $stmt_check->bind_param("i", $id_tipo_dispositivo);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows === 0) {
            Logger::warning("Tipo de dispositivo no existe", ['type_id' => $id_tipo_dispositivo]);
            throw new Exception('El tipo de dispositivo seleccionado no existe');
        }
        $stmt_check->close();

        // 2. Insertar nuevo dispositivo
        $now = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $encendido_timestamp = $esta_encendido ? $now->format('Y-m-d H:i:s') : null;
        
        Logger::debug("Preparando inserción de dispositivo", [
            'user_id' => $usuario_id,
            'status' => $esta_encendido ? 'encendido' : 'apagado'
        ]);

        $sql = "INSERT INTO dispositivos 
                (usuario_id, nombre, id_tipo_dispositivo, ubicacion, esta_encendido, encendido, apagado) 
                VALUES (?, ?, ?, ?, ?, ?, NULL)";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            Logger::error("Error al preparar consulta de inserción", [
                'error' => $conexion->error,
                'query' => $sql
            ]);
            throw new Exception('Error al preparar la consulta: ' . $conexion->error);
        }

        $stmt->bind_param("isssis", 
            $usuario_id, 
            $nombre, 
            $id_tipo_dispositivo, 
            $ubicacion, 
            $esta_encendido,
            $encendido_timestamp
        );
        
        if (!$stmt->execute()) {
            Logger::error("Error al ejecutar inserción", [
                'error' => $stmt->error,
                'params' => [$usuario_id, $nombre, $id_tipo_dispositivo]
            ]);
            throw new Exception('Error al agregar dispositivo: ' . $stmt->error);
        }

        $device_id = $stmt->insert_id;
        Logger::info("Dispositivo registrado exitosamente", [
            'device_id' => $device_id,
            'name' => $nombre,
            'location' => $ubicacion,
            'status' => $esta_encendido
        ]);

        return [
            'success' => true,
            'dispositivo_id' => $device_id,
            'message' => 'Dispositivo agregado correctamente',
            'encendido' => $esta_encendido ? $now->format('M j, Y g:i A') : null,
            'encendido_timestamp' => $encendido_timestamp,
            'esta_encendido' => $esta_encendido,
            'apagado' => null
        ];

    } catch (Exception $e) {
        Logger::error("Error en agregarDispositivo", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    } finally {
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}

// Verificar sesión
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    Logger::warning("Intento de acceso no autorizado a agregarDispositivo", [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
    ]);
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Manejar la solicitud
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario_id = $_SESSION['user_id'];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $isJson = strpos($contentType, 'application/json') !== false;

        Logger::info("Solicitud para agregar dispositivo", [
            'method' => 'POST',
            'content_type' => $contentType,
            'user_id' => $usuario_id
        ]);

        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Logger::error("Error al decodificar JSON", [
                    'error' => json_last_error_msg()
                ]);
                throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
            }
            
            $nombre = $input['nombre'] ?? '';
            $id_tipo_dispositivo = $input['id_tipo_dispositivo'] ?? '';
            $ubicacion = $input['ubicacion'] ?? null;
            $esta_encendido = $input['esta_encendido'] ?? false;
        } else {
            $nombre = $_POST['nombre'] ?? '';
            $id_tipo_dispositivo = $_POST['id_tipo_dispositivo'] ?? '';
            $ubicacion = $_POST['ubicacion'] ?? null;
            $esta_encendido = isset($_POST['esta_encendido']) ? (bool)$_POST['esta_encendido'] : false;
        }

        // Validar que id_tipo_dispositivo es numérico
        if (!is_numeric($id_tipo_dispositivo)) {
            Logger::warning("Tipo de dispositivo no numérico", [
                'type_id' => $id_tipo_dispositivo
            ]);
            throw new Exception('El tipo de dispositivo debe ser un valor numérico');
        }

        $resultado = agregarDispositivo(
            $usuario_id, 
            $nombre, 
            (int)$id_tipo_dispositivo, 
            $ubicacion,
            $esta_encendido
        );
        
        if ($isJson) {
            http_response_code(201);
            echo json_encode($resultado);
        } else {
            $_SESSION['success_message'] = $resultado['message'];
            Logger::info("Redireccionando a dispositivos.php");
            header('Location: dispositivos.php');
        }
        exit();
    }

    Logger::warning("Método no permitido", [
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();

} catch (Exception $e) {
    if ($isJson) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        $_SESSION['error_message'] = $e->getMessage();
        Logger::error("Redireccionando con error", [
            'error' => $e->getMessage()
        ]);
        header('Location: agregar_dispositivo.php');
    }
    exit();
}