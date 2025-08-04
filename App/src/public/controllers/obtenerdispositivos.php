<?php
session_start();
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

function obtenerDispositivos($usuario_id = null, $id_tipo_dispositivo = null, $con_info_tipo = true) {
    $conexion = conectarDB();
    $dispositivos = [];

    try {
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
        } else {
            throw new Exception('Usuario no autenticado');
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

        if (!empty($params)) {
            // bind_param requiere referencias, no un array directo, por eso hacemos esto:
            $bind_names[] = $types;
            for ($i=0; $i<count($params); $i++) {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name; // referencia
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
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

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit();
    }

    if (isset($_SESSION['usuario_id'])) {
        $usuario_id = (int)$_SESSION['usuario_id'];
    } else {
        throw new Exception('No autenticado');
    }

    $id_tipo_dispositivo = isset($_GET['id_tipo_dispositivo']) ? (int)$_GET['id_tipo_dispositivo'] : null;

    $dispositivos = obtenerDispositivos($usuario_id, $id_tipo_dispositivo);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $dispositivos,
        'count' => count($dispositivos)
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
