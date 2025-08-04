<?php
session_start();
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }

    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('No autorizado');
    }

    $usuario_id = (int)$_SESSION['usuario_id'];

    $conexion = conectarDB();

    $stmt = $conexion->prepare("SELECT nombre, email FROM usuarios WHERE id_usuario = ?");
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . $conexion->error);
    }

    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($usuario = $result->fetch_assoc()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $usuario
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
    }

    $stmt->close();
    $conexion->close();

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
