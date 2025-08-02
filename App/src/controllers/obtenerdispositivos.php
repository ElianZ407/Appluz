<?php
require_once __DIR__ . '/conexion.php';

// Configuración inicial
header('Content-Type: application/json');

/**
 * Función para obtener dispositivos
 * @param int|null $usuario_id Filtrar por usuario (opcional)
 * @param int|null $id_tipo_dispositivo Filtrar por tipo (opcional)
 * @param bool $con_info_tipo Incluir información del tipo de dispositivo
 * @return array Lista de dispositivos
 */


function obtenerDispositivos($usuario_id = null, $id_tipo_dispositivo = null, $con_info_tipo = true) {
    $conexion = conectarDB();
    $dispositivos = [];
    
    try {
        // Construir la consulta SQL
        $sql = "SELECT d.*";
        
        if ($con_info_tipo) {
            $sql .= ", td.nombre as nombre_tipo, td.descripcion, td.w as consumo_w";
        }
        
        $sql .= " FROM dispositivos d";
        
        if ($con_info_tipo) {
            $sql .= " JOIN tipo_dispositivo td ON d.id_tipo_dispositivo = td.id_tipo_dispositivo";
        }
        
        $where = [];
        $params = [];
        $types = '';
        
        if (!empty($usuario_id)) {
            $where[] = "d.usuario_id = ?";
            $params[] = $usuario_id;
            $types .= 'i';
        }
        
        if (!empty($id_tipo_dispositivo)) {
            $where[] = "d.id_tipo_dispositivo = ?";
            $params[] = $id_tipo_dispositivo;
            $types .= 'i';
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY d.nombre";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Error al preparar la consulta: ' . $conexion->error);
        }
        
        // Bind parameters si hay
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Formatear los datos
            $dispositivo = [
                'id_dispositivo' => $row['id_dispositivo'],
                'nombre' => $row['nombre'],
                'ubicacion' => $row['ubicacion'],
                'esta_encendido' => (bool)$row['esta_encendido'],
                'id_tipo_dispositivo' => $row['id_tipo_dispositivo']
            ];
            
            if ($con_info_tipo) {
                $dispositivo['tipo'] = [
                    'nombre' => $row['nombre_tipo'],
                    'descripcion' => $row['descripcion'],
                    'consumo_w' => $row['consumo_w']
                ];
            }
            
            $dispositivos[] = $dispositivo;
        }
        
        return $dispositivos;
        
    } finally {
        if (isset($stmt)) $stmt->close();
        $conexion->close();
    }
}

// Manejar la solicitud
try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }
    
    // Obtener parámetros
    $usuario_id = isset($_GET['usuario_id']) ? (int)$_GET['usuario_id'] : null;
    $id_tipo_dispositivo = isset($_GET['id_tipo_dispositivo']) ? (int)$_GET['id_tipo_dispositivo'] : null;
    
    
    $dispositivos = obtenerDispositivos($usuario_id, $id_tipo_dispositivo);
    
    // Responder
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $dispositivos,
        'count' => count($dispositivos)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>