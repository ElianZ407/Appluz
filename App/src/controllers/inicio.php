<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/logger.php'; // Incluimos el logger

// Configuración inicial
header('Content-Type: application/json');

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Función para iniciar sesión
 * @param string $email Correo electrónico
 * @param string $password Contraseña
 * @return array Resultado de la operación
 */
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
        
        // Buscar usuario en la base de datos
        $sql = "SELECT id_usuario, email, nombre, password FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            Logger::error('Error al preparar consulta de login', [
                'error' => $conexion->error,
                'email' => $email
            ]);
            throw new Exception('Error al preparar la consulta: ' . $conexion->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            Logger::warning('Intento de login con email no registrado', ['email' => $email]);
            throw new Exception('Usuario o contraseña incorrectos');
        }

        $usuario = $result->fetch_assoc();
        
        // Verificar contraseña
        if (!password_verify($password, $usuario['password'])) {
            Logger::warning('Intento de login con contraseña incorrecta', [
                'email' => $email,
                'user_id' => $usuario['id_usuario']
            ]);
            throw new Exception('Usuario o contraseña incorrectos');
        }

        // Configurar datos de sesión
        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['user_email'] = $usuario['email'];
        $_SESSION['user_nombre'] = $usuario['nombre'];
        $_SESSION['logged_in'] = true;

        // Registrar login exitoso
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

// Determinar el tipo de contenido
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$isJson = strpos($contentType, 'application/json') !== false;

// Manejar la solicitud
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        Logger::debug("Solicitud de login recibida", [
            'method' => 'POST',
            'content_type' => $contentType
        ]);

        if ($isJson) {
            // Manejar JSON
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
            // Manejar formulario tradicional
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Logger::warning('Email inválido proporcionado', ['email' => $email]);
            throw new Exception('Por favor ingresa un email válido');
        }

        // Intentar inicio de sesión
        $resultado = loginUser($email, $password);
        
        if ($isJson) {
            // Respuesta JSON
            http_response_code(200);
            echo json_encode($resultado);
            exit();
        } else {
            // Redirigir para formulario HTML
            header('Location: dashboard.php');
            exit();
        }
    }

    // Si no es POST
    Logger::warning('Intento de acceso al login con método no permitido', [
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();

} catch (Exception $e) {
    if ($isJson) {
        // Respuesta JSON de error
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        // Para formulario HTML, redirigir con mensaje de error
        $_SESSION['login_error'] = $e->getMessage();
        header('Location: inicio.php');
        exit();
    }
}

// Si ya está logueado y accede directamente al script
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    Logger::info('Usuario ya autenticado intentó acceder al login', [
        'user_id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null
    ]);
    
    if ($isJson) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya hay una sesión activa'
        ]);
    } else {
        header('Location: dashboard.php');
    }
    exit();
}