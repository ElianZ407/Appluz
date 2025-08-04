<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $conexion = conectarDB();

    // Obtenemos métricas del usuario actual (por ejemplo con $_SESSION['usuario_id'])
    session_start();
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    if (!$usuario_id) {
        throw new Exception("No autorizado");
    }

    $sql = "SELECT mes, año, kwh_usados, dispositivos_activos, gasto_estimado, fecha 
            FROM metricas_ahorro 
            WHERE usuario_id = ? 
            ORDER BY año DESC, mes DESC
            LIMIT 3";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $metricas = [];
    while ($row = $result->fetch_assoc()) {
        // Para mostrar mes con nombre en español
        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $mesNombre = $meses[$row['mes'] - 1] ?? $row['mes'];

        $metricas[] = [
            'mes' => $mesNombre,
            'anio' => $row['año'],
            'kwh_usados' => (float)$row['kwh_usados'],
            'dispositivos_activos' => (int)$row['dispositivos_activos'],
            'gasto_estimado' => (float)$row['gasto_estimado'],
            'fecha' => $row['fecha']
        ];
    }

    echo json_encode(['success' => true, 'data' => $metricas]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
