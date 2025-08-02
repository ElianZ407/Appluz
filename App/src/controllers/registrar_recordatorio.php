<?php
require_once __DIR__ . '/conexion.php';

// Configuración inicial
header('Content-Type: application/json');
date_default_timezone_set('America/Mexico_City');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function registrarRecordatorio($dispositivo_id, $hora, $mensaje, $activo = true) {
    // Validaciones
    if (empty($dispositivo_id)) throw new Exception('ID de dispositivo requerido');
    if (empty($hora)) throw new Exception('La hora es requerida');
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
        throw new Exception('Formato de hora inválido. Use HH:MM (24 horas)');
    }
    if (empty($mensaje)) throw new Exception('El mensaje es requerido');

    $conexion = conectarDB();
    $stmt_check = null;
    $stmt = null;
    
    try {
       
        $offset = (new DateTime())->format('P');
        $conexion->query("SET time_zone = '$offset'");
        
        
        $sql_check = "SELECT id_dispositivo FROM dispositivos 
                     WHERE id_dispositivo = ? AND usuario_id = ?";
        $stmt_check = $conexion->prepare($sql_check);
        
        if (!$stmt_check) throw new Exception('Error al verificar dispositivo: ' . $conexion->error);

        $stmt_check->bind_param("ii", $dispositivo_id, $_SESSION['user_id']);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows === 0) {
            throw new Exception('Dispositivo no encontrado o no pertenece al usuario');
        }
        
        
        $sql = "INSERT INTO recordatorios (dispositivo_id, hora, mensaje, activo) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) throw new Exception('Error al preparar la consulta: ' . $conexion->error);

        $activo_bool = filter_var($activo, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
        $stmt->bind_param("issi", $dispositivo_id, $hora, $mensaje, $activo_bool);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al registrar recordatorio: ' . $stmt->error);
        }

        return [
            'success' => true,
            'recordatorio_id' => $stmt->insert_id,
            'message' => 'Recordatorio registrado correctamente'
        ];

    } finally {
        
        if ($stmt_check !== null && is_object($stmt_check)) $stmt_check->close();
        if ($stmt !== null && is_object($stmt)) $stmt->close();
        if ($conexion !== null) $conexion->close();
    }
}


if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $isJson = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
        $input = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;

        if ($isJson && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }

        $dispositivo_id = $input['dispositivo_id'] ?? '';
        $hora = $input['hora'] ?? '';
        $mensaje = $input['mensaje'] ?? '';
        $activo = $input['activo'] ?? true;

        if (!is_numeric($dispositivo_id)) {
            throw new Exception('El ID de dispositivo debe ser numérico');
        }

        $resultado = registrarRecordatorio((int)$dispositivo_id, $hora, $mensaje, $activo);
        
        if ($isJson) {
            http_response_code(201);
            echo json_encode($resultado);
        } else {
            $_SESSION['success_message'] = $resultado['message'];
            header('Location: recordatorios.php');
        }
        exit();
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();

} catch (Exception $e) {
    $isJson = $isJson ?? false;
    if ($isJson) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: agregar_recordatorio.php');
    }
    exit();
}