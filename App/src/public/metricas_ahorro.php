<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Métricas de Ahorro - Apaga la luz</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 flex">

  <!-- BARRA DE NAVEGACIÓN LATERAL -->
  <aside class="w-20 md:w-48 bg-white shadow-md flex flex-col items-center py-6 space-y-6">
    <nav class="flex flex-col gap-6 text-center text-3xl text-gray-700">
      <a href="#" class="flex flex-col items-center hover:text-purple-700">
        <button onclick="abrirModal()" class="text-3xl">➕</button>
        <span class="text-xs font-medium mt-1 hidden md:block">Agregar dispositivo</span>
      </a>

      <a href="#" onclick="abrirModalRecordatorio()" class="flex flex-col items-center hover:text-purple-700">
        <span>📝</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Agregar recordatorio</span>
      </a>

      <a href="home.php" class="flex flex-col items-center hover:text-purple-700">
        <span>🔌</span>
        <span class="text-xs mt-1 hidden md:block">Dispositivos</span>
      </a>

      <a href="metricas_ahorro.php" class="flex flex-col items-center text-purple-700 font-bold">
        <span>⚡</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Métricas de Ahorro</span>
      </a>

      <a href="perfil_usuario.php" class="flex flex-col items-center hover:text-purple-700">
        <span>👤</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Cuenta</span>
      </a>
    </nav>
  </aside>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="flex-1 ml-20 md:ml-48 p-8">

    <h1 class="text-4xl font-bold mb-8 text-center text-purple-700">Métricas de Ahorro</h1>

    <!-- Gráfica -->
    <div class="bg-white rounded-xl shadow p-4 mb-10 max-w-4xl mx-auto">
      <canvas id="graficaMetricas" class="w-full h-64"></canvas>
    </div>

    <!-- Tarjetas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 id="titulo-consumo" class="text-xl font-bold text-purple-600 mb-2">Consumo</h3>
        <p id="valor-consumo" class="text-2xl font-semibold text-gray-700">Cargando...</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-xl font-bold text-purple-600 mb-2">Dispositivos activos</h3>
        <p id="valor-dispositivos" class="text-2xl font-semibold text-gray-700">Cargando...</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-xl font-bold text-purple-600 mb-2">Gasto estimado (MXN)</h3>
        <p id="valor-gasto" class="text-2xl font-semibold text-gray-700">Cargando...</p>
      </div>
    </div>

    <!-- Botón para regresar -->
    <div class="text-center mt-10">
      <a href="home.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
        ← Volver al inicio
      </a>
    </div>

  </main>

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
<!-- GRÁFICA -->
<script>
async function cargarMetricas() {
  try {
    const res = await fetch('./controllers/get_metricas.php', { credentials: 'include' });
    const json = await res.json();

    if (!json.success) throw new Error(json.message);

    const datos = json.data;

    if(datos.length === 0) {
      document.getElementById('valor-consumo').textContent = 'No hay datos';
      document.getElementById('valor-dispositivos').textContent = 'No hay datos';
      document.getElementById('valor-gasto').textContent = 'No hay datos';
      return;
    }

    // Ordenar cronológicamente (del más antiguo al más reciente)
    datos.sort((a,b) => (a.anio - b.anio) || (a.mes.localeCompare(b.mes)));

    // Extraer etiquetas y datos para gráfica
    const labels = datos.map(m => `${m.mes} ${m.anio}`);
    const consumo = datos.map(m => m.kwh_usados);
    const dispositivos = datos.map(m => m.dispositivos_activos);
    const gasto = datos.map(m => m.gasto_estimado);

    // Crear gráfica con Chart.js
    new Chart(document.getElementById('graficaMetricas').getContext('2d'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'kWh Usados',
          data: consumo,
          backgroundColor: ['#60A5FA', '#34D399', '#FBBF24'],
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true, position: 'top' }
        }
      }
    });

    // Último dato para tarjetas
    const ultimo = datos[datos.length - 1];

    // Actualizar tarjetas con valores reales y pesos mexicanos
    document.getElementById('titulo-consumo').textContent = `Consumo de ${ultimo.mes} ${ultimo.anio}`;
    document.getElementById('valor-consumo').textContent = `${ultimo.kwh_usados.toFixed(2)} kWh`;
    document.getElementById('valor-dispositivos').textContent = ultimo.dispositivos_activos;
    document.getElementById('valor-gasto').textContent = `$${ultimo.gasto_estimado.toFixed(2)} MXN`;

  } catch (error) {
    console.error('Error cargando métricas:', error);
    document.getElementById('valor-consumo').textContent = 'Error al cargar datos';
    document.getElementById('valor-dispositivos').textContent = 'Error al cargar datos';
    document.getElementById('valor-gasto').textContent = 'Error al cargar datos';
  }
}

cargarMetricas();

// Funciones para abrir/cerrar modales
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