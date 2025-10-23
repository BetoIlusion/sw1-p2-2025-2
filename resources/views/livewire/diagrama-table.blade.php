<div>
    <div class="py-6">
        <div class="w-full px-4 mx-auto">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    {{-- TABLA PRINCIPAL DE DIAGRAMAS --}}
                    <table class="w-full divide-y divide-gray-700">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="w-1/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ID</th>
                                <th scope="col" class="w-3/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nombre</th>
                                <th scope="col" class="w-4/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Descripción</th>
                                <th scope="col" class="w-2/12 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Fecha de creación</th>
                                <th scope="col" class="w-2/12 px-6 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($diagramas as $item)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ $item->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><span class="truncate">{{ $item->descripcion }}</span></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{-- ACCIONES CON BOTONES RESTAURADOS --}}
                                        <div class="flex items-center justify-center space-x-4">
                                            {{-- BOTÓN EDITAR / SHOW --}}
                                            <a href="{{ route('diagramas.show', $item->id) }}" class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-green-200 hover:text-green-700 focus:outline-none" title="Editar diagrama">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 010 2.828l-9.9 9.9a1 1 0 01-.39.242l-4 1.333a1 1 0 01-1.263-1.263l1.333-4a1 1 0 01.242-.39l9.9-9.9a2 2 0 012.828 0z" /></svg>
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
                                            {{-- El botón de gestionar usuarios solo se muestra si el usuario es 'creador' --}}
                                            @if ($tipoUsuario === 'creador')
                                                {{-- BOTÓN LISTA DE USUARIOS --}}
                                                <button wire:click="openModal({{ $item->id }})" class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-blue-200 hover:text-blue-700 focus:outline-none" title="Gestionar colaboradores">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                                </button>
                                            @endif
                                            {{-- BOTÓN ELIMINAR --}}
                                            <form action="{{ route('diagramas.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este diagrama?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center justify-center p-2 transition-colors duration-200 bg-gray-100 rounded-lg text-gray-500 hover:bg-red-200 hover:text-red-700 focus:outline-none" title="Eliminar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
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

    {{-- MODAL PARA GESTIONAR COLABORADORES --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" x-data="{ show: @entangle('showModal') }" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            
            {{-- Ventana del Modal con ancho fijo de 400px --}}
            <div class="bg-white rounded-lg shadow-xl" style="width: 400px;" @click.away="show = false">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Gestionar Colaboradores</h3>
                    <p class="text-sm text-gray-500">Diagrama: "{{ $nombreDiagramaSeleccionado }}"</p>
                </div>

                <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto">
                    
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Colaboradores Actuales ({{ count($users) }})</h4>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full">
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($users as $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-3 text-sm text-gray-700">{{ $user->name }}</td>
                                            <td class="p-3 w-16 text-right">
                                                <button wire:click="eliminarUsuario({{ $user->id }})" wire:loading.attr="disabled" class="text-red-500 hover:text-red-700" title="Eliminar colaborador">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="p-4 text-center text-sm text-gray-500">No hay colaboradores.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Usuarios Disponibles ({{ count($usuariosSinRelacion) }})</h4>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full">
                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($usuariosSinRelacion as $user)
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-3 text-sm text-gray-700">{{ $user->name }}</td>
                                            <td class="p-3 w-16 text-right">
                                                <button wire:click="agregarUsuario({{ $user->id }})" wire:loading.attr="disabled" class="text-green-500 hover:text-green-700" title="Añadir como colaborador">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="p-4 text-center text-sm text-gray-500">Todos los usuarios han sido agregados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                 <div class="px-6 pb-2">
                    @if (session()->has('message'))
                        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded relative text-sm" role="alert">
                            <span class="block sm:inline">{{ session('message') }}</span>
                        </div>
                    @elseif (session()->has('error'))
                         <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded relative text-sm" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-end rounded-b-lg">
                    <button @click="show = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 text-sm font-medium">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>