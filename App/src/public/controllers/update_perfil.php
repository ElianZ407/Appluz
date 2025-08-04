<?php
session_start();
require_once __DIR__ . '/conexion.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;

if (!$usuario_id) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'PUT') {
    $nombre = $_POST['nombre'] ?? null;
    $correo = $_POST['correo'] ?? null;
    $contrasena = $_POST['contrasena'] ?? null;

    if ($correo !== null && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Correo electrónico no válido']);
        exit;
    }

    $conn = conectarDB();

    // Construir la consulta dinámicamente
    $fields = [];
    $params = [];
    $types = '';

    if ($nombre !== null && $nombre !== '') {
        $fields[] = "nombre = ?";
        $params[] = $nombre;
        $types .= 's';
    }

    if ($correo !== null && $correo !== '') {
        $fields[] = "email = ?";
        $params[] = $correo;
        $types .= 's';
    }

    if ($contrasena !== null && $contrasena !== '') {
        $fields[] = "password = ?";
        $contrasenaHash = password_hash($contrasena, PASSWORD_BCRYPT);
        $params[] = $contrasenaHash;
        $types .= 's';
    }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos para actualizar']);
        exit;
    }

    $sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id_usuario = ?";
    $params[] = $usuario_id;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar perfil']);
    }

    $stmt->close();
    $conn->close();

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
