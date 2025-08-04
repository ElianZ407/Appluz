<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/logger.php';

header('Content-Type: application/json');

function agregarDispositivo($usuario_id, $nombre, $id_tipo_dispositivo, $ubicacion = null, $esta_encendido = false) {
    date_default_timezone_set('America/Mexico_City');

    Logger::info("Inicio de registro de dispositivo", [
        'usuario_id' => $usuario_id,
        'nombre' => $nombre,
        'tipo' => $id_tipo_dispositivo
    ]);

    if (empty($usuario_id) || empty($nombre) || empty($id_tipo_dispositivo)) {
        Logger::error("Faltan campos requeridos");
        throw new Exception('Faltan campos requeridos');
    }

    $conexion = conectarDB();

    try {
        // Validar tipo de dispositivo
        $sql_check = "SELECT id_tipo_dispositivo FROM tipo_dispositivo WHERE id_tipo_dispositivo = ?";
        $stmt_check = $conexion->prepare($sql_check);
        $stmt_check->bind_param("i", $id_tipo_dispositivo);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows === 0) {
            Logger::warning("Tipo de dispositivo no existe", ['id_tipo' => $id_tipo_dispositivo]);
            throw new Exception('El tipo de dispositivo seleccionado no existe');
        }
        $stmt_check->close();

        // Insertar dispositivo
        $now = new DateTime('now', new DateTimeZone('America/Mexico_City'));
        $encendido_timestamp = $esta_encendido ? $now->format('Y-m-d H:i:s') : null;

        $sql = "INSERT INTO dispositivos (usuario_id, nombre, id_tipo_dispositivo, ubicacion, esta_encendido, encendido, apagado) 
                VALUES (?, ?, ?, ?, ?, ?, NULL)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isssis", $usuario_id, $nombre, $id_tipo_dispositivo, $ubicacion, $esta_encendido, $encendido_timestamp);
        $stmt->execute();

        $device_id = $stmt->insert_id;

        Logger::info("Dispositivo registrado", ['dispositivo_id' => $device_id]);

        return [
            'success' => true,
            'dispositivo_id' => $device_id,
            'message' => 'Dispositivo agregado correctamente',
            'encendido' => $encendido_timestamp,
            'esta_encendido' => $esta_encendido,
            'apagado' => null
        ];

    } catch (Exception $e) {
        Logger::error("Error agregando dispositivo", ['error' => $e->getMessage()]);
        throw $e;
    } finally {
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}

// --- Controlador principal ---
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario_id = $_SESSION['usuario_id'];
        $nombre = $_POST['nombre'] ?? '';
        $id_tipo_dispositivo = $_POST['id_tipo_dispositivo'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? null;
        $esta_encendido = isset($_POST['esta_encendido']) ? 1 : 0;

        if (!is_numeric($id_tipo_dispositivo)) {
            throw new Exception('El tipo de dispositivo debe ser numérico');
        }

        $resultado = agregarDispositivo(
            $usuario_id,
            $nombre,
            (int)$id_tipo_dispositivo,
            $ubicacion,
            $esta_encendido
        );

        $_SESSION['success_message'] = $resultado['message'];
        header('Location: /Appluz/App/src/public/home.php');
        exit();

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: /Appluz/App/src/public/home.html');
    exit();
}
