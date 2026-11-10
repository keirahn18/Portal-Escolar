<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - E.P.E.P. N° 436</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-slate-800">Panel Administrativo</h1>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-600">Hola, {{ Auth::user()->nombre }} ({{ Auth::user()->rol->nombre }})</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-red-600 hover:underline">Cerrar Sesión</button>
            </form>
        </div>
    </nav>
    <main class="p-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-800">Bienvenido/a al sistema de gestión escolar</h2>
            <p class="text-slate-600 mt-2">Institución: {{ Auth::user()->institucion->nombre }}</p>
        </div>
    </main>
</body>
</html>