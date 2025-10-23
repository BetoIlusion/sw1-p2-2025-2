<div>
    <a href="#" wire:click.prevent="openModal"
        class="inline-flex items-center px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white text-base font-semibold rounded-md shadow-lg transition duration-150 ease-in-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Crear Diagrama
    </a>

    @if ($showModal)
        <div
            class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 transition-opacity duration-300">
            <div
                class="relative w-96 p-6 rounded-lg bg-white border-2 border-yellow-500 shadow-[0_0_30px_rgba(255,223,0,0.6)] before:absolute before:inset-0 before:rounded-lg before:border-4 before:border-yellow-300 before:pointer-events-none">
                <div class="text-center relative z-10">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b-2 border-yellow-500 pb-2">
                        Nuevo Diagrama
                    </h3>

                    <form action="{{ route('diagramas.store') }}" method="POST" class="text-gray-600 mb-6">
                        @csrf
                        <div class="mb-4">
                            <label for="insertar nombre"
                                class="block text-gray-700 text-sm font-bold mb-2 text-left">Nombre</label>
                            <input type="text" id="nombre" name="nombre"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                placeholder="Nombre del diagrama" required>
                        </div>
                        <div class="mb-6">
                            <label for="descripcion"
                                class="block text-gray-700 text-sm font-bold mb-2 text-left">Descripción</label>
                            <textarea id="descripcion" name="descripcion" rows="3"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                placeholder="Descripción del diagrama"></textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md font-medium transition duration-150">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 font-semibold transition duration-150">
                                Crear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
