<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Perfil de Usuario</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 flex">

  <!-- BARRA LATERAL -->
  <aside class="w-20 md:w-48 bg-white shadow-md flex flex-col items-center py-6 space-y-6">
    <nav class="flex flex-col gap-6 text-center text-3xl text-gray-700">
      <a href="#" class="flex flex-col items-center hover:text-purple-700">
        <span>➕</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Agregar dispositivo</span>
      </a>
      <a href="#" class="flex flex-col items-center hover:text-purple-700">
        <span>📝</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Agregar recordatorio</span>
      </a>
      <a href="home.php" class="flex flex-col items-center hover:text-purple-700">
        <span>🔌</span>
        <span class="text-xs mt-1 hidden md:block">Dispositivos</span>
      </a>
      <a href="metricas_ahorro.php" class="flex flex-col items-center hover:text-purple-700">
        <span>⚡</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Métricas de Ahorro</span>
      </a>
      <a href="perfil_usuario.php" class="flex flex-col items-center text-purple-700 font-bold">
        <span>👤</span>
        <span class="text-xs font-medium mt-1 hidden md:block">Cuenta</span>
      </a>
    </nav>
  </aside>

  <!-- CONTENIDO -->
  <main class="flex-1 ml-20 md:ml-48 p-8">
    <h1 class="text-4xl font-bold mb-8 text-center text-purple-700">Perfil del Usuario</h1>

    <div class="bg-white rounded-xl shadow p-6 max-w-xl mx-auto space-y-6">
      <div>
        <label class="text-sm text-gray-500 font-medium">Nombre</label>
        <p id="nombre-text" class="text-xl font-semibold text-gray-800 mt-1">Cargando...</p>
      </div>

      <div>
        <label class="text-sm text-gray-500 font-medium">Correo Electrónico</label>
        <p id="correo-text" class="text-xl font-semibold text-gray-800 mt-1">Cargando...</p>
      </div>

      <div>
        <label class="text-sm text-gray-500 font-medium">Contraseña</label>
        <p class="text-xl font-semibold text-gray-800 mt-1">********</p>
      </div>

      <!-- Botón para abrir el modal -->
      <div class="text-center pt-4">
        <button onclick="document.getElementById('modal').classList.remove('hidden')" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">
          Editar Perfil
        </button>
      </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
      <div class="bg-white rounded-xl p-6 w-full max-w-md space-y-4 shadow-lg relative">
        <button onclick="document.getElementById('modal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 text-2xl">&times;</button>

        <h2 class="text-xl font-bold text-purple-700 text-center mb-4">Editar Perfil</h2>

        <form id="formEditarPerfil" class="space-y-6">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="accion" value="actualizar_perfil">

            <div>
              <label class="text-sm text-gray-500 font-medium">Nombre</label>
              <input type="text" name="nombre" id="input-nombre" class="w-full px-4 py-2 border rounded" >
            </div>

            <div>
              <label class="text-sm text-gray-500 font-medium">Correo Electrónico</label>
              <input type="email" name="correo" id="input-correo" class="w-full px-4 py-2 border rounded" >
            </div>

            <div>
              <label class="text-sm text-gray-500 font-medium">Contraseña nueva</label>
              <input type="password" name="contrasena" id="input-contrasena" class="w-full px-4 py-2 border rounded" >
            </div>

            <div class="text-center pt-4">
              <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">
                Guardar Cambios
              </button>
            </div>
          </form>
      </div>
    </div>

    <!-- Botón para regresar -->
    <div class="text-center mt-10">
      <a href="home.php" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
        ← Volver al inicio
      </a>
    </div>
  </main>

 <script>
  async function cargarDatos() {
    try {
      const resp = await fetch('./controllers/get_perfil.php', {
        method: 'GET',
        credentials: 'include'  // importante para enviar cookie de sesión
      });
      const json = await resp.json();

      if (!json.success) throw new Error(json.message || 'Error desconocido');

      const usuario = json.data;

      document.getElementById('nombre-text').textContent = usuario.nombre;
      document.getElementById('correo-text').textContent = usuario.email;

      document.getElementById('input-nombre').value = usuario.nombre;
      document.getElementById('input-correo').value = usuario.email;
    } catch (error) {
      alert('Error al cargar datos: ' + error.message);
    }
  }

  cargarDatos();

  document.getElementById('formEditarPerfil').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    // Eliminar campos vacíos para no enviarlos
    for (const [key, value] of formData.entries()) {
      if (value.trim() === '') {
        formData.delete(key);
      }
    }

    try {
      const resp = await fetch('./controllers/update_perfil.php', {
        method: 'POST',
        credentials: 'include',
        body: formData
      });

      const json = await resp.json();

      if (json.success) {
        alert('Perfil actualizado correctamente');
        document.getElementById('modal').classList.add('hidden');
        cargarDatos(); // Recargar datos actualizados
      } else {
        alert('Error al actualizar perfil: ' + (json.error || 'Desconocido'));
      }
    } catch (error) {
      alert('Error en la petición: ' + error.message);
    }
  });
  
</script>


</body>
</html>
