<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/logger.php'; // Incluimos el logger

// Configuración inicial
header('Content-Type: application/json');

function registrarUsuario($email, $nombre, $password) {
    
    if (empty($email) || empty($password)) {
        Logger::error('Intento de registro sin email o contraseña', [
            'email_provided' => !empty($email),
            'password_provided' => !empty($password)
        ]);
        throw new Exception('Email y contraseña son obligatorios');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Logger::warning('Formato de email inválido en registro', ['email' => $email]);
        throw new Exception('El formato del email no es válido');
    }

    if (strlen($password) < 8) {
        Logger::warning('Contraseña demasiado corta en registro', [
            'email' => $email,
            'password_length' => strlen($password)
        ]);
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }

    $conexion = conectarDB();
    
    try {
        Logger::info("Iniciando registro de usuario", ['email' => $email]);
        
       
        $sql_check = "SELECT id_usuario FROM usuarios WHERE email = ?";
        $stmt_check = $conexion->prepare($sql_check);
        
        if (!$stmt_check) {
            Logger::error('Error al preparar consulta de verificación', [
                'error' => $conexion->error,
                'email' => $email
            ]);
            throw new Exception('Error al preparar la consulta: ' . $conexion->error);
        }

        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            Logger::warning('Intento de registro con email existente', ['email' => $email]);
            throw new Exception('El email ya está registrado');
        }
        $stmt_check->close();

        // Hashear la contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        Logger::debug("Contraseña hasheada para registro", [
            'email' => $email,
            'hash' => '...' // No registrar el hash real por seguridad
        ]);

        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (email, nombre, password) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            Logger::error('Error al preparar consulta de inserción', [
                'error' => $conexion->error,
                'email' => $email
            ]);
            throw new Exception('Error al preparar la consulta: ' . $conexion->error);
        }

        $stmt->bind_param("sss", $email, $nombre, $password_hashed);
        
        if (!$stmt->execute()) {
            Logger::error('Error al ejecutar registro de usuario', [
                'error' => $stmt->error,
                'email' => $email
            ]);
            throw new Exception('Error al registrar usuario: ' . $stmt->error);
        }

        $user_id = $stmt->insert_id;
        Logger::info("Usuario registrado exitosamente", [
            'user_id' => $user_id,
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
        ]);

        return [
            'success' => true,
            'user_id' => $user_id,
            'message' => 'Usuario registrado correctamente'
        ];

    } catch (Exception $e) {
        Logger::error("Error en proceso de registro", [
            'email' => $email,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    } finally {
        // Cerrar conexiones en cualquier caso
        if (isset($stmt_check)) $stmt_check->close();
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}

// Manejo de la solicitud
try {
    // Determinar el tipo de contenido
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isJson = strpos($contentType, 'application/json') !== false;
    
    Logger::debug("Solicitud de registro recibida", [
        'method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $contentType,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'desconocida'
    ]);
    
    if ($isJson) {
        // Manejar JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error('Error al decodificar JSON en registro', [
                'error' => json_last_error_msg()
            ]);
            throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
        }
        
        $email = isset($input['email']) ? trim($input['email']) : '';
        $nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
        $password = isset($input['password']) ? $input['password'] : '';
    } else {
        // Manejar formulario tradicional
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Verificar confirmación de contraseña para formularios
        if (!isset($_POST['confirm_password']) || $password !== $_POST['confirm_password']) {
            Logger::warning('Las contraseñas no coinciden en formulario de registro', [
                'email' => $email
            ]);
            throw new Exception('Las contraseñas no coinciden');
        }
    }
    
    // Registrar usuario
    $resultado = registrarUsuario($email, $nombre, $password);
    
    if ($isJson) {
        http_response_code(201);
        echo json_encode($resultado);
    } else {
       
        Logger::info("Redireccionando a registro_exitoso.php", [
            'user_id' => $resultado['user_id']
        ]);
        header('Location: registro_exitoso.php?user_id=' . $resultado['user_id']);
        exit();
    }
    
} catch (Exception $e) {
    if ($isJson) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } else {
        Logger::warning("Redireccionando con error de registro", [
            'error' => $e->getMessage()
        ]);
        header('Location: registro.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>