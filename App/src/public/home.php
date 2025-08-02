<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Dispositivos - Apaga la luz</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="bg-purple-50 min-h-screen flex text-gray-800">

  <!-- Sidebar -->
  <aside class="w-20 md:w-48 bg-white shadow-md flex flex-col items-center py-6 space-y-6">

    
<nav class="flex flex-col gap-6 text-center text-3xl text-gray-700">
  <!-- Agregar dispositivo -->
  <a href="#" class="flex flex-col items-center hover:text-purple-700">
    <button onclick="abrirModal()" class="text-3xl">➕</button>
    <span class="text-xs font-medium mt-1 hidden md:block">Agregar dispositivo</span>
  </a>

<!-- Agregar recordatorio -->
<a href="#" onclick="abrirModalRecordatorio()" class="flex flex-col items-center hover:text-purple-700">
  <span>📝</span>
  <span class="text-xs font-medium mt-1 hidden md:block">Agregar recordatorio</span>
</a>


  <!-- Dispositivos -->
<a href="home.php" class="flex flex-col items-center <?php echo $currentPage === 'home.php' ? 'text-purple-700 font-bold' : 'hover:text-purple-700'; ?>">
  <span>🔌</span>
  <span class="text-xs mt-1 hidden md:block">Dispositivos</span>
</a>


<!-- Ahorro -->
<a href="?vista=ahorro" class="flex flex-col items-center hover:text-purple-700 <?php echo isset($_GET['vista']) && $_GET['vista'] === 'ahorro' ? 'text-purple-700 font-bold' : ''; ?>">
  <span>⚡</span>
  <span class="text-xs font-medium mt-1 hidden md:block">Métricas de Ahorro</span>
</a>


  <!-- Cuenta -->
  <a href="#" class="flex flex-col items-center hover:text-purple-700">
    <span>👤</span>
    <span class="text-xs font-medium mt-1 hidden md:block">Cuenta</span>
  </a>
</nav>


  </aside>

  <!-- Main content -->
  <main class="flex-1 p-6">
    <?php if (isset($_GET['vista']) && $_GET['vista'] === 'ahorro'): ?>
  <h1 class="text-2xl font-bold mb-4">Métricas de Ahorro</h1>
  <section id="metricas" class="mt-10">
  <h2 class="text-xl font-bold mb-4">Consumo energético</h2>
  <canvas id="graficaConsumo" class="bg-white p-4 rounded-lg shadow-md"></canvas>
</section>

  <?php
    require_once '../controllers/conexion.php';
    session_start();
    $conexion = conectarDB();
    $userId = $_SESSION['user_id'];

    $query = "SELECT fecha, kwh_usados, gasto_estimated, dispositivos_activos
              FROM metricas_ahorro
              WHERE usuario_id = ?
              ORDER BY fecha DESC LIMIT 12";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $resultado = $stmt->get_result();
  ?>

  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border rounded-lg">
      <thead class="bg-purple-100 text-purple-800 text-sm font-semibold">
        <tr>
          <th class="py-2 px-4 text-left">Fecha</th>
          <th class="py-2 px-4 text-left">kWh usados</th>
          <th class="py-2 px-4 text-left">Gasto estimado</th>
          <th class="py-2 px-4 text-left">Dispositivos activos</th>
        </tr>
      </thead>
      <tbody class="text-sm">
        <?php while ($fila = $resultado->fetch_assoc()): ?>
          <tr class="border-t">
            <td class="py-2 px-4"><?php echo htmlspecialchars($fila['fecha']); ?></td>
            <td class="py-2 px-4"><?php echo htmlspecialchars($fila['kwh_usados']); ?> kWh</td>
            <td class="py-2 px-4">$<?php echo htmlspecialchars($fila['gasto_estimated']); ?></td>
            <td class="py-2 px-4"><?php echo htmlspecialchars($fila['dispositivos_activos']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

<?php else: ?>
  <!-- Aquí va tu sección de dispositivos (ya la tienes definida) -->
<?php endif; ?>

    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Dispositivos</h1>
    </div>

    <!-- Filtros -->
    <div class="flex space-x-2 mb-6">
      <button class="px-4 py-1 border border-gray-300 rounded-full text-sm bg-white hover:bg-gray-100">Todos</button>
      <button class="px-4 py-1 border border-purple-600 bg-purple-100 text-purple-800 font-medium rounded-full text-sm">Sin apagar</button>
      <button class="px-4 py-1 border border-gray-300 rounded-full text-sm bg-white hover:bg-gray-100">Apagar</button>
    </div>

    <!-- Grid de dispositivos -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <!-- Tarjeta de dispositivo -->
      <div class="bg-white p-4 rounded-xl shadow-sm">
        <div class="h-24 bg-purple-100 rounded mb-2"></div>
        <h3 class="text-sm font-semibold">TV Sala</h3>
        <p class="text-xs text-gray-500">Apagar a las: 5:00PM</p>
      </div>

      <div class="bg-white p-4 rounded-xl shadow-sm">
        <div class="h-24 bg-purple-100 rounded mb-2"></div>
        <h3 class="text-sm font-semibold">Microondas</h3>
        <p class="text-xs text-gray-500">Apagar a las: 8:00PM</p>
      </div>

      <div class="bg-white p-4 rounded-xl shadow-sm">
        <div class="h-24 bg-purple-100 rounded mb-2"></div>
        <h3 class="text-sm font-semibold">Lámpara estudio</h3>
        <p class="text-xs text-gray-500">Actualizado hoy</p>
      </div>

      <!-- Puedes duplicar más tarjetas aquí -->
    </div>
  </main>
<!-- Modal: Agregar Dispositivo -->
<div id="modalDispositivo" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
    <h2 class="text-xl font-semibold mb-4 text-purple-700">Agregar nuevo dispositivo</h2>

    <form action="../controllers/agregar.php" method="POST" class="space-y-4">
      <!-- Nombre del dispositivo -->
      <div>
        <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del dispositivo<span class="text-red-500">*</span></label>
        <input type="text" name="nombre" id="nombre" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
      </div>

      <!-- Tipo de dispositivo -->
      <div>
        <label for="id_tipo_dispositivo" class="block text-sm font-medium text-gray-700">Tipo de dispositivo<span class="text-red-500">*</span></label>
        <select name="id_tipo_dispositivo" id="id_tipo_dispositivo" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
          <option value="">Seleccione un tipo</option>
          <option value="1">TV</option>
          <option value="2">Microondas</option>
          <option value="3">Lámpara</option>
          <!-- Puedes llenar estos valores dinámicamente desde PHP o BD -->
        </select>
      </div>

      <!-- Ubicación -->
      <div>
        <label for="ubicacion" class="block text-sm font-medium text-gray-700">Ubicación (opcional)</label>
        <input type="text" name="ubicacion" id="ubicacion" class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
      </div>

      <!-- Estado inicial -->
      <div class="flex items-center space-x-2">
        <input type="checkbox" name="esta_encendido" id="esta_encendido" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
        <label for="esta_encendido" class="text-sm text-gray-700">Encendido al registrar</label>
      </div>

      <!-- Botones -->
      <div class="flex justify-end pt-4">
        <button type="button" onclick="cerrarModal()" class="mr-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
        <button type="submit" class="bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800 text-sm">Guardar</button>
      </div>
    </form>
  </div>
</div>
<!-- Modal: Agregar Recordatorio -->
<div id="modalRecordatorio" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
    <h2 class="text-xl font-semibold mb-4 text-purple-700">Agregar recordatorio</h2>

    <form action="../controllers/registrar_recordatorio.php" method="POST" class="space-y-4">
      <!-- Dispositivo -->
      <div>
        <label for="dispositivo_id" class="block text-sm font-medium text-gray-700">Dispositivo<span class="text-red-500">*</span></label>
        <select name="dispositivo_id" id="dispositivo_id" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
          <option value="">Seleccione un dispositivo</option>
          <!-- Opciones deben generarse dinámicamente desde PHP -->
          <option value="1">TV Sala</option>
          <option value="2">Microondas</option>
          <option value="3">Lámpara</option>
        </select>
      </div>

      <!-- Hora -->
      <div>
        <label for="hora" class="block text-sm font-medium text-gray-700">Hora (formato 24h)<span class="text-red-500">*</span></label>
        <input type="time" name="hora" id="hora" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
      </div>

      <!-- Mensaje -->
      <div>
        <label for="mensaje" class="block text-sm font-medium text-gray-700">Mensaje<span class="text-red-500">*</span></label>
        <textarea name="mensaje" id="mensaje" rows="3" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600"></textarea>
      </div>

      <!-- Activo -->
      <div class="flex items-center space-x-2">
        <input type="checkbox" name="activo" id="activo" checked class="h-4 w-4 text-purple-600 border-gray-300 rounded">
        <label for="activo" class="text-sm text-gray-700">Recordatorio activo</label>
      </div>

      <!-- Botones -->
      <div class="flex justify-end pt-4">
        <button type="button" onclick="cerrarModalRecordatorio()" class="mr-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
        <button type="submit" class="bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800 text-sm">Guardar</button>
      </div>
    </form>
  </div>
</div>

</div>
<script>
  function abrirModal() {
    document.getElementById('modalDispositivo').classList.remove('hidden');
    document.getElementById('modalDispositivo').classList.add('flex');
  }

  function cerrarModal() {
    document.getElementById('modalDispositivo').classList.remove('flex');
    document.getElementById('modalDispositivo').classList.add('hidden');
  }
    function abrirModalRecordatorio() {
    document.getElementById('modalRecordatorio').classList.remove('hidden');
    document.getElementById('modalRecordatorio').classList.add('flex');
  }

  function cerrarModalRecordatorio() {
    document.getElementById('modalRecordatorio').classList.remove('flex');
    document.getElementById('modalRecordatorio').classList.add('hidden');
  }
</script>

</body>
</html>
