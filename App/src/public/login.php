<!-- login-tailwind.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Apaga la luz - Iniciar sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="flex justify-between items-center bg-white px-6 py-3 shadow">
        <div class="text-xl">🔆</div>
        <div class="space-x-2">
            <a href="registro.php" class="px-3 py-1 bg-gray-800 text-white rounded text-sm hover:bg-gray-700">Regístrate</a>
        </div>
    </nav>

    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-sm">
            <div class="text-center mb-6">
                <div class="text-4xl">📱</div>
                <h2 class="mt-2 text-2xl font-bold">Apaga la luz</h2>
            </div>
            <form method="POST" action="login.php" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium">Email</label>
                    <input type="email" name="email" class="w-full mt-1 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-gray-700" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Contraseña</label>
                    <input type="password" name="password" class="w-full mt-1 p-2 border rounded focus:outline-none focus:ring-2 focus:ring-gray-700" required>
                </div>
                <button type="submit" class="w-full bg-gray-800 text-white py-2 rounded hover:bg-gray-700">Inicia sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
