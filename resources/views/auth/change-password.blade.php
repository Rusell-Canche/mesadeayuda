<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('assets/qr.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
<div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0">

    <div class="mb-8">
        <img src="{{ asset('assets/mesadeayuda.png') }}" alt="Logo" width="400" height="100">
    </div>

    <div class="px-6 py-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg w-full sm:max-w-md">

        <!-- Aviso temporal -->
        <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-400 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-yellow-800">Contraseña temporal detectada</p>
                    <p class="text-sm text-yellow-700 mt-1">Por seguridad, debes establecer una nueva contraseña antes de continuar.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf

            <!-- Nueva contraseña -->
            <div class="mt-2 relative">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nueva Contraseña
                </label>
                <input id="password"
                    class="p-2 mt-2 rounded-xl border w-full focus:border-indigo-500 @error('password') border-red-500 @enderror"
                    type="password" name="password" placeholder="Mínimo 8 caracteres" required>
                <button type="button" class="absolute right-3 top-10" onclick="toggleVisibility('password', 'icon1')">
                    <i id="icon1" class="fas fa-eye-slash text-gray-400 hover:text-red-900"></i>
                </button>
                @error('password')
                    <div class="mt-1 text-red-600 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirmar contraseña -->
            <div class="mt-4 relative">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Confirmar Contraseña
                </label>
                <input id="password_confirmation"
                    class="p-2 mt-2 rounded-xl border w-full focus:border-indigo-500"
                    type="password" name="password_confirmation" placeholder="Repite tu contraseña" required>
                <button type="button" class="absolute right-3 top-10" onclick="toggleVisibility('password_confirmation', 'icon2')">
                    <i id="icon2" class="fas fa-eye-slash text-gray-400 hover:text-red-900"></i>
                </button>
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-900 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-700">
                    Guardar nueva contraseña
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    }
</script>
</body>
</html>