<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/logger.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $recordatorio_id = $input['recordatorio_id'] ?? null;
    $dispositivo_id = $input['dispositivo_id'] ?? null;

    if (!$recordatorio_id || !$dispositivo_id) {
        throw new Exception("Datos incompletos");
    }

    $conexion = conectarDB();
    $conexion->begin_transaction();

    // 1. Obtener info del dispositivo
    $sqlInfo = "SELECT d.encendido, d.usuario_id, td.w 
                FROM dispositivos d 
                JOIN tipo_dispositivo td ON d.id_tipo_dispositivo = td.id_tipo_dispositivo 
                WHERE d.id_dispositivo = ?";
    $stmtInfo = $conexion->prepare($sqlInfo);
    $stmtInfo->bind_param("i", $dispositivo_id);
    if (!$stmtInfo->execute()) throw new Exception("Error al obtener datos del dispositivo");
    $result = $stmtInfo->get_result();
    if ($result->num_rows === 0) throw new Exception("Dispositivo no encontrado");

    $row = $result->fetch_assoc();
    $encendido = new DateTime($row['encendido']);
    $usuario_id = $row['usuario_id'];
    $watts = $row['w'];

    // 2. Marcar apagado
    $sql1 = "UPDATE dispositivos SET apagado = NOW() WHERE id_dispositivo = ?";
    $stmt1 = $conexion->prepare($sql1);
    $stmt1->bind_param("i", $dispositivo_id);
    if (!$stmt1->execute()) throw new Exception("Error al apagar dispositivo");

    // 3. Calcular tiempo de uso en horas
    $apagado = new DateTime(); // ahora
    $interval = $encendido->diff($apagado);
    $horas = ($interval->days * 24) + ($interval->h) + ($interval->i / 60) + ($interval->s / 3600);

    // 4. Calcular kWh usados y gasto en pesos mexicanos
    $kwh_usados = ($watts / 1000) * $horas;
    $costo_kwh = 3.5; // tarifa promedio en pesos mexicanos por kWh
    $gasto_estimado = round($kwh_usados * $costo_kwh, 2);

    // 5. Insertar en metricas_ahorro
    $sqlInsert = "INSERT INTO metricas_ahorro (usuario_id, fecha, kwh_usados, gasto_estimado, dispositivos_activos, mes, año) 
                  VALUES (?, NOW(), ?, ?, 1, ?, ?)";
    $mes = (int) $apagado->format('m');
    $anio = (int) $apagado->format('Y');
    $stmtInsert = $conexion->prepare($sqlInsert);
    $stmtInsert->bind_param("idiii", $usuario_id, $kwh_usados, $gasto_estimado, $mes, $anio);
    if (!$stmtInsert->execute()) throw new Exception("Error al insertar métrica de ahorro");

    // 6. Desactivar recordatorio
    $sql2 = "UPDATE recordatorios SET activo = 0 WHERE id_recordatorio = ?";
    $stmt2 = $conexion->prepare($sql2);
    $stmt2->bind_param("i", $recordatorio_id);
    if (!$stmt2->execute()) throw new Exception("Error al desactivar recordatorio");

    $conexion->commit();

    Logger::info("Dispositivo apagado y métrica registrada", [
        'recordatorio_id' => $recordatorio_id,
        'dispositivo_id' => $dispositivo_id,
        'usuario_id' => $usuario_id,
        'kwh_usados' => $kwh_usados,
        'gasto_estimado' => $gasto_estimado
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conexion)) $conexion->rollback();
    Logger::error("Error al apagar y registrar métrica", ['error' => $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
