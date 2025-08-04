<?php
session_start(); // Siempre iniciar sesión

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/logger.php';

function loginUser($email, $password) {
    if (empty($email)) {
        Logger::error('Intento de login sin email');
        throw new Exception('El email es requerido');
    }

    if (empty($password)) {
        Logger::error('Intento de login sin contraseña', ['email' => $email]);
        throw new Exception('La contraseña es requerida');
    }

    $conexion = conectarDB();

    try {
        Logger::info("Intento de login iniciado", ['email' => $email]);

        $sql = "SELECT id_usuario, email, nombre, password FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            Logger::error('Error al preparar consulta de login', [
                'error' => $conexion->error,
                'email' => $email
            ]);
            throw new Exception('Error al preparar la consulta');
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            Logger::warning('Intento de login con email no registrado', ['email' => $email]);
            throw new Exception('Usuario o contraseña incorrectos');
        }

        $usuario = $result->fetch_assoc();

        if (!password_verify($password, $usuario['password'])) {
            Logger::warning('Intento de login con contraseña incorrecta', [
                'email' => $email,
                'user_id' => $usuario['id_usuario']
            ]);
            throw new Exception('Usuario o contraseña incorrectos');
        }

        Logger::info("Login exitoso", [
            'user_id' => $usuario['id_usuario'],
            'email' => $usuario['email'],
            'ip' => $_SERVER['REMOTE_ADDR']
        ]);

        return [
            'success' => true,
            'user_id' => $usuario['id_usuario'],
            'email' => $usuario['email'],
            'nombre' => $usuario['nombre'],
            'message' => 'Inicio de sesión exitoso'
        ];

    } catch (Exception $e) {
        Logger::error("Error en proceso de login", [
            'email' => $email,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    } finally {
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}

// Determinar tipo de solicitud
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJson = strpos($contentType, 'application/json') !== false;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Logger::warning('Intento de acceso al login con método no permitido', [
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
        exit();
    }

    Logger::debug("Solicitud de login recibida", [
        'method' => 'POST',
        'content_type' => $contentType
    ]);

    if ($isJson) {
        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error('Error al decodificar JSON en login', [
                'error' => json_last_error_msg()
            ]);
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Logger::warning('Email inválido proporcionado', ['email' => $email]);
        throw new Exception('Por favor ingresa un email válido');
    }

    $resultado = loginUser($email, $password);

    // Guardar sesión (para ambos casos)
    $_SESSION['usuario_id'] = $resultado['user_id'];
    $_SESSION['email'] = $resultado['email'];
    $_SESSION['nombre'] = $resultado['nombre'];

    if ($isJson) {
        http_response_code(200);
        echo json_encode($resultado);
    } else {
        header('Location: ../home.php');
    }

    exit();

} catch (Exception $e) {
    if ($isJson) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        $mensaje = urlencode($e->getMessage());
        header("Location: inicio.php?error=$mensaje");
    }
    exit();
}
