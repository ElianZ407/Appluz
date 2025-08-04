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
      <a href="metricas_ahorro.php" class="flex flex-col items-center hover:text-purple-700">
        <span>⚡</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Métricas de Ahorro</span>
      </a>

      <a href="#" onclick="abrirModalVerRecordatorios()" class="flex flex-col items-center hover:text-purple-700">
        <span>🔔</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Ver recordatorios</span>
      </a>


      <!-- Cuenta -->
      <a href="perfil_usuario.php" class="flex flex-col items-center hover:text-purple-700">
        <span>👤</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Cuenta</span>
      </a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="flex-1 p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Dispositivos</h1>
    </div>

    <!-- Filtros -->
    <div class="flex space-x-2 mb-6">
      <button onclick="filtrarDispositivos('todos')" id="btn-todos" class="px-4 py-1 border border-gray-300 rounded-full text-sm bg-white hover:bg-gray-100">Todos</button>
      <button onclick="filtrarDispositivos('encendidos')" id="btn-encendidos" class="px-4 py-1 border border-gray-600 bg-purple-100 text-purple-800 font-medium rounded-full text-sm">Sin apagar</button>
      <button onclick="filtrarDispositivos('apagados')" id="btn-apagados" class="px-4 py-1 border border-gray-300 rounded-full text-sm bg-white hover:bg-gray-100">Apagar</button>
    </div>

    <!-- Grid de dispositivos (vacío, se llenará con JS) -->
    <div id="contenedorDispositivos" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <!-- Aquí se inyectarán las tarjetas -->
    </div>
  </main>

  <!-- Modal: Agregar Dispositivo -->
  <div id="modalDispositivo" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
      <h2 class="text-xl font-semibold mb-4 text-purple-700">Agregar nuevo dispositivo</h2>
      <form action="./controllers/agregar.php" method="POST" class="space-y-4">
        <div>
          <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del dispositivo<span class="text-red-500">*</span></label>
          <input type="text" name="nombre" id="nombre" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
        </div>
        <div>
          <label for="id_tipo_dispositivo" class="block text-sm font-medium text-gray-700">Tipo de dispositivo<span class="text-red-500">*</span></label>
          <select name="id_tipo_dispositivo" id="id_tipo_dispositivo" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
            <option value="">Seleccione un tipo</option>
            <option value="1">TV</option>
            <option value="2">Microondas</option>
            <option value="3">Lámpara</option>
            <option value="4">Computadora</option>
          </select>
        </div>
        <div>
          <label for="ubicacion" class="block text-sm font-medium text-gray-700">Ubicación (opcional)</label>
          <input type="text" name="ubicacion" id="ubicacion" class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" name="esta_encendido" id="esta_encendido" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
          <label for="esta_encendido" class="text-sm text-gray-700">Encendido al registrar</label>
        </div>
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
      <form action="./controllers/registrar_recordatorio.php" method="POST" class="space-y-4">
        <div>
          <label for="dispositivo_id" class="block text-sm font-medium text-gray-700">Dispositivo<span class="text-red-500">*</span></label>
          <select name="dispositivo_id" id="dispositivo_id" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
          <option value="">Seleccione un dispositivo</option>
</select>

        </div>
        <div>
          <label for="hora" class="block text-sm font-medium text-gray-700">Hora (formato 24h)<span class="text-red-500">*</span></label>
          <input type="time" name="hora" id="hora" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600">
        </div>
        <div>
          <label for="mensaje" class="block text-sm font-medium text-gray-700">Mensaje<span class="text-red-500">*</span></label>
          <textarea name="mensaje" id="mensaje" rows="3" required class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-purple-600"></textarea>
        </div>
        <div class="flex items-center space-x-2">
          <input type="checkbox" name="activo" id="activo" checked class="h-4 w-4 text-purple-600 border-gray-300 rounded">
          <label for="activo" class="text-sm text-gray-700">Recordatorio activo</label>
        </div>
        <div class="flex justify-end pt-4">
          <button type="button" onclick="cerrarModalRecordatorio()" class="mr-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
          <button type="submit" class="bg-purple-700 text-white px-4 py-2 rounded hover:bg-purple-800 text-sm">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Ver Recordatorios -->
<div id="modalVerRecordatorios" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
  <div class="bg-white rounded-2xl shadow-xl w-11/12 max-w-md p-6 relative">
    
    <!-- Cerrar -->
    <button onclick="cerrarModalVerRecordatorios()" class="absolute top-2 right-3 text-gray-500 hover:text-red-600 text-xl">&times;</button>

    <h2 class="text-xl font-bold mb-4 text-center text-purple-700">📋 Recordatorios</h2>

    <div id="contenedorRecordatorios" class="space-y-3 max-h-64 overflow-y-auto">
      <!-- Aquí se insertan los recordatorios -->
      <p class="text-sm text-gray-500">Cargando recordatorios...</p>
    </div>

    <div class="mt-5 text-center">
      <button onclick="cerrarModalVerRecordatorios()" class="bg-purple-600 text-white px-4 py-2 rounded-xl hover:bg-purple-700">
        Cerrar
      </button>
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
    llenarSelectDispositivos(); // Llenar el select con dispositivos encendidos
    document.getElementById('modalRecordatorio').classList.remove('hidden');
    document.getElementById('modalRecordatorio').classList.add('flex');
  }

  function cerrarModalRecordatorio() {
    document.getElementById('modalRecordatorio').classList.remove('flex');
    document.getElementById('modalRecordatorio').classList.add('hidden');
  }

  // Crea tarjeta HTML para un dispositivo
  function crearTarjetaDispositivo(dispositivo) {
    const div = document.createElement('div');
    div.className = "bg-white p-4 rounded-xl shadow-sm";

    const colorFondo = dispositivo.esta_encendido ? 'bg-green-100' : 'bg-red-100';

    div.innerHTML = `
      <div class="h-24 ${colorFondo} rounded mb-2"></div>
      <h3 class="text-sm font-semibold">${dispositivo.nombre}</h3>
      <p class="text-xs text-gray-500">
        ${dispositivo.ubicacion ? dispositivo.ubicacion : 'Sin ubicación'}<br>
        Estado: ${dispositivo.esta_encendido ? 'Encendido' : 'Apagado'}
      </p>
    `;
    return div;
  }

  // Variables para filtro
  let dispositivos = [];
  let filtroActual = 'encendidos'; // Por defecto mostrar "Sin apagar"

  // Carga dispositivos desde API
  async function cargarDispositivos() {
    try {
      const resp = await fetch('./controllers/obtenerdispositivos.php');
      const data = await resp.json();

      if (!data.success) {
        console.error('Error al cargar dispositivos:', data.message);
        return;
      }

      dispositivos = data.data;
      mostrarDispositivosFiltrados();

    } catch (error) {
      console.error('Error en fetch:', error);
    }
  }

  // Llenar el select con dispositivos encendidos
function llenarSelectDispositivos() {
  const select = document.getElementById('dispositivo_id');
  select.innerHTML = '<option value="">Seleccione un dispositivo</option>'; // Limpiar y dejar opción default

  const encendidos = dispositivos.filter(d => d.esta_encendido);

  encendidos.forEach(d => {
    const option = document.createElement('option');
    option.value = d.id_dispositivo;
    option.textContent = d.nombre;
    select.appendChild(option);
  });

  if (encendidos.length === 0) {
    const option = document.createElement('option');
    option.disabled = true;
    option.textContent = 'No hay dispositivos encendidos';
    select.appendChild(option);
  }
}

function abrirModalVerRecordatorios() {
  document.getElementById('modalVerRecordatorios').classList.remove('hidden');
  cargarRecordatorios();
}

function cerrarModalVerRecordatorios() {
  document.getElementById('modalVerRecordatorios').classList.add('hidden');
}
function cargarRecordatorios() {
  const contenedor = document.getElementById('contenedorRecordatorios');
  contenedor.innerHTML = '<p class="text-sm text-gray-500">Cargando...</p>';

  fetch('./controllers/mostrar_recordatorio.php')
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        contenedor.innerHTML = `<p class="text-red-600 text-sm">${data.message}</p>`;
        return;
      }

      if (data.recordatorios.length === 0) {
        contenedor.innerHTML = '<p class="text-gray-500 text-sm">No hay recordatorios registrados.</p>';
        return;
      }

      contenedor.innerHTML = '';

      data.recordatorios.forEach(rec => {
        const div = document.createElement('div');
        div.className = 'p-4 border rounded-xl shadow-sm bg-white flex flex-col gap-2';

        div.innerHTML = `
          <div class="flex justify-between items-center">
            <div>
              <p class="text-sm text-gray-700"><strong>Dispositivo:</strong> ${rec.nombre_dispositivo || 'N/A'}</p>
              <p class="text-sm text-gray-700"><strong>Lugar:</strong> ${rec.ubicacion || 'N/A'}</p>
              <p class="text-sm"><strong>Hora:</strong> ${rec.hora}</p>
              <p class="text-sm"><strong>Mensaje:</strong> ${rec.mensaje}</p>
              <p class="text-sm"><strong>Activo:</strong> ${rec.activo == 1 ? '✅' : '❌'}</p>
            </div>
            <button 
              onclick="apagarRecordatorio(${rec.id}, ${rec.dispositivo_id})"
              class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium px-3 py-1 rounded-lg"
            >
              Apagar
            </button>
          </div>
        `;


        contenedor.appendChild(div);
      });
    })
    .catch(error => {
      contenedor.innerHTML = `<p class="text-red-600 text-sm">Error al cargar: ${error}</p>`;
    });
}

function apagarRecordatorio(recordatorio_id, dispositivo_id) {
  if (!confirm('¿Estás seguro de que quieres apagar este recordatorio?')) return;

  fetch('./controllers/apagar_recordatorio.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ recordatorio_id, dispositivo_id })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Recordatorio apagado correctamente.');
      cargarRecordatorios(); // Vuelve a cargar la lista
    } else {
      alert('Error al apagar: ' + (data.message || 'Error desconocido'));
    }
  })
  .catch(error => {
    console.error('Error en la petición:', error);
    alert('Error al apagar el recordatorio.');
  });
}




  // Muestra solo dispositivos filtrados en el contenedor
  function mostrarDispositivosFiltrados() {
    const contenedor = document.getElementById('contenedorDispositivos');
    contenedor.innerHTML = '';

    // Filtrar según filtroActual
    let filtrados = [];
    if (filtroActual === 'todos') {
      filtrados = dispositivos;
    } else if (filtroActual === 'encendidos') {
      filtrados = dispositivos.filter(d => d.esta_encendido);
    } else if (filtroActual === 'apagados') {
      filtrados = dispositivos.filter(d => !d.esta_encendido);
    }

    if (filtrados.length === 0) {
      contenedor.innerHTML = '<p class="text-gray-600">No hay dispositivos para mostrar.</p>';
      return;
    }

    filtrados.forEach(d => {
      const tarjeta = crearTarjetaDispositivo(d);
      contenedor.appendChild(tarjeta);
    });
  }

  // Cambiar filtro y actualizar botones visualmente
  function filtrarDispositivos(filtro) {
    filtroActual = filtro;
    // Actualizar estilos de botones
    ['todos', 'encendidos', 'apagados'].forEach(id => {
      const btn = document.getElementById('btn-' + id);
      if (btn) {
        if (id === filtro) {
          btn.classList.add('border-purple-600', 'bg-purple-100', 'text-purple-800', 'font-medium');
          btn.classList.remove('border-gray-300', 'bg-white', 'hover:bg-gray-100');
        } else {
          btn.classList.remove('border-purple-600', 'bg-purple-100', 'text-purple-800', 'font-medium');
          btn.classList.add('border-gray-300', 'bg-white', 'hover:bg-gray-100');
        }
      }
    });

    mostrarDispositivosFiltrados();
  }

  window.addEventListener('DOMContentLoaded', () => {
    cargarDispositivos();
  });
</script>

</body>
</html>
