@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Consultas Derivadas del Portal</h1>
            <p class="text-xs text-slate-500">Atención a familias, historial de mensajes y re-derivación exclusiva entre docentes.</p>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <select name="estado" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white">
                <option value="">Todos los estados</option>
                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option>
                <option value="en_atencion" @selected(request('estado') === 'en_atencion')>En atención</option>
                <option value="cerrada" @selected(request('estado') === 'cerrada')>Cerradas</option>
            </select>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($consultas as $consulta)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-4">
                <!-- Cabecera del ticket -->
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-xs font-extrabold text-sky-700 bg-sky-50 px-2.5 py-1 rounded-lg">
                            {{ $consulta->codigo_seguimiento }}
                        </span>
                        <span class="ml-2 text-sm font-bold text-slate-800">{{ $consulta->asunto }}</span>
                        <p class="text-xs text-slate-500 mt-1">
                            Consultante: <strong>{{ $consulta->nombre_consultante }}</strong> ({{ $consulta->contacto }}) •
                            Asignado a: <strong>{{ $consulta->docenteAsignado ? $consulta->docenteAsignado->nombre . ' ' . $consulta->docenteAsignado->apellido : 'Sin asignar' }}</strong>
                        </p>
                    </div>

                    <span class="text-[11px] font-bold uppercase px-2.5 py-1 rounded-full
                        {{ $consulta->estado === 'pendiente' ? 'bg-amber-100 text-amber-800' : ($consulta->estado === 'en_atencion' ? 'bg-sky-100 text-sky-800' : 'bg-slate-200 text-slate-700') }}">
                        {{ str_replace('_', ' ', $consulta->estado) }}
                    </span>
                </div>

                <!-- Hilo de mensajes -->
                <div class="bg-slate-50 rounded-xl p-3.5 space-y-2.5 max-h-60 overflow-y-auto">
                    @foreach($consulta->mensajes as $msg)
                        <div class="text-xs {{ $msg->emisor === 'docente' ? 'pl-4 border-l-2 border-sky-600' : 'pl-4 border-l-2 border-amber-500' }}">
                            <p class="font-bold text-slate-700">
                                {{ $msg->emisor === 'docente' && $msg->usuario ? 'Prof. ' . $msg->usuario->nombre . ' ' . $msg->usuario->apellido : $consulta->nombre_consultante }}
                                <span class="font-normal text-slate-400 ml-1">{{ $msg->fecha->format('d/m/Y H:i') }}</span>
                            </p>
                            <p class="text-slate-600 mt-0.5 whitespace-pre-line">{{ $msg->contenido }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- Acciones: Responder y Re-derivar a otro docente -->
                @if($consulta->estado !== 'cerrada')
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-1">
                        <!-- Form Responder -->
                        <form method="POST" action="{{ route('admin.consultas.responder', $consulta->id_consulta) }}" class="lg:col-span-2 space-y-2">
                            @csrf
                            <textarea name="contenido" rows="2" required placeholder="Escribí una respuesta para la familia..."
                                class="w-full text-xs border border-slate-300 rounded-xl p-2.5 focus:outline-none focus:border-sky-600"></textarea>
                            <div class="flex items-center justify-between">
                                <label class="inline-flex items-center gap-1.5 text-xs text-slate-600">
                                    <input type="checkbox" name="cerrar" value="1" class="rounded border-slate-300">
                                    Cerrar consulta al enviar
                                </label>
                                <button type="submit" class="bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold px-4 py-2 rounded-xl cursor-pointer">
                                    Enviar respuesta
                                </button>
                            </div>
                        </form>

                        <!-- Form Re-derivar (solo docentes) -->
                        <form method="POST" action="{{ route('admin.consultas.rederivar', $consulta->id_consulta) }}" class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-2">
                            @csrf
                            <p class="text-[11px] font-bold text-slate-700">Derivar a otro docente</p>
                            <select name="fk_id_usuario" required class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white">
                                <option value="">Seleccionar docente...</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id_usuario }}">{{ $docente->apellido }}, {{ $docente->nombre }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="motivo" placeholder="Motivo breve (opcional)"
                                class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-1.5 bg-white">
                            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold py-1.5 rounded-lg cursor-pointer">
                                Re-derivar consulta
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white border border-slate-200 rounded-2xl p-8 text-center text-xs text-slate-500">
                No hay consultas registradas con ese filtro.
            </div>
        @endforelse
    </div>

    <div>
        {{ $consultas->links() }}
    </div>
</div>
@endsection
