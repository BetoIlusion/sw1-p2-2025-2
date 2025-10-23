<div>
    <div class="py-6">
        <div class="w-full px-4 mx-auto"> <!-- Cambiado a ancho completo con padding -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-700"> <!-- Cambiado a ancho completo -->
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col"
                                    class="w-1/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col"
                                    class="w-3/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Nombre
                                </th>
                                <th scope="col"
                                    class="w-4/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th scope="col"
                                    class="w-2/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Fecha de creación
                                </th>
                                <th scope="col"
                                    class="w-2/12 px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($diagramas as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">
                                        {{ $item->nombre }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <span class="truncate">{{ $item->descripcion }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </td>

                                    {{-- ACCIONES CONFIGS --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center justify-center space-x-6">

                                            {{-- BOTÓN EDITAR / SHOW --}}
                                            <a href="{{ route('diagramas.show', $item->id) }}"
                                                class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-green-200 hover:text-green-700 focus:outline-none"
                                                title="Editar diagrama">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path
                                                        d="M17.414 2.586a2 2 0 010 2.828l-9.9 9.9a1 1 0 01-.39.242l-4 1.333a1 1 0 01-1.263-1.263l1.333-4a1 1 0 01.242-.39l9.9-9.9a2 2 0 012.828 0z" />
                                                </svg>
                                            </a>
                                            {{-- BOTÓN EXPORTAR --}}
                                            <a href="{{ route('diagramas.exportarSpringBoot', $item->id) }}"
                                                class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-purple-200 hover:text-purple-700 focus:outline-none"
                                                title="Exportar Diagrama">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>

                                            {{-- BOTÓN LISTA DE USUARIOS --}}
                                            <button wire:click="openModal({{ $item->id }})"
                                                class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-blue-200 hover:text-blue-700 focus:outline-none"
                                                title="Lista de usuarios">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </button>
                                            {{-- BOTÓN COMPARTIR --}}
                                            <a href="{{ route('diagramas.compartir', $item->id) }}"
                                                class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-blue-200 hover:text-blue-700 focus:outline-none"
                                                title="Compartir Diagrama">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                </svg>
                                            </a>

                                            {{-- BOTÓN ELIMINAR --}}
                                            <form action="{{ route('diagramas.destroy', $item->id) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('¿Estás seguro de eliminar este diagrama?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-red-200 hover:text-red-700 focus:outline-none"
                                                    title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>
                                            
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        @if ($tipoUsuario === 'creador')
                                            No has creado ningún diagrama todavía.
                                        @else
                                            Nadie ha compartido un diagrama contigo todavía.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>

    @if ($showModal)
        <div x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-90 translate-y-4"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <!-- Contenedor del modal -->
            <div class="relative w-80 h-80 bg-white rounded-lg shadow-lg border border-yellow-400 overflow-hidden">

                <!-- Encabezado -->
                <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-2">
                    <h3 class="text-sm font-bold text-white text-center flex items-center justify-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Usuarios del Diagrama
                    </h3>
                </div>

                <!-- Contenido -->
                <div class="p-2 max-h-48 overflow-y-auto">
                    @if (count($users) > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach ($users as $user)
                                <li class="py-1 px-2 flex items-center hover:bg-yellow-50 rounded transition">
                                    <div
                                        class="flex-shrink-0 h-6 w-6 rounded-full bg-yellow-200 flex items-center justify-center text-yellow-800 font-semibold text-xs">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="ml-2 text-sm text-gray-800">{{ $user->name }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="py-4 text-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto text-gray-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="mt-1 text-xs">No hay usuarios asociados</p>
                        </div>
                    @endif
                </div>

                <!-- Botón -->
                <div class="flex justify-end p-2 border-t border-gray-200">
                    <button @click="show = false; setTimeout(() => { $wire.closeModal() }, 200)"
                        class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md font-semibold text-sm transition duration-150">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
