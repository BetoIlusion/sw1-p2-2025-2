<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{-- {{ __('Dashboard') }} --}}
            </h2>
            <div class="flex items-center">
                {{-- Boton importar con su ubicacion establecida --}}
                @livewire('import-button')
                {{-- Boton Crear Diagrama con su ubicacion establecida --}}
                @livewire('create-diagram')

            </div>
        </div>
    </x-slot>
     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Notificaciones en tiempo real --}}
            @if (session('livewire_message'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('livewire_message') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                
                {{-- Sección Mis Diagramas --}}
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800">Mis Diagramas</h1>
                </div>
                @livewire('diagrama-table', ['tipoUsuario' => 'creador'])

                {{-- Sección Colaborador --}}
                <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800">Colaborador</h1>
                </div>
                @livewire('diagrama-table', ['tipoUsuario' => 'colaborador'])

            </div>
        </div>
    </div>
  {{-- Script para WebSockets Específicos --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Escuchar eventos específicos
            window.Echo.channel('diagramas')
                .listen('.diagrama.eliminado', (e) => {
                    console.log('Diagrama eliminado via socket:', e.diagramaId);
                    // Despachamos un evento para que Livewire lo capture
                    Livewire.dispatch('diagrama-eliminado-externamente', { diagramaId: e.diagramaId });
                    // Mostrar notificación global
                    showNotification('Un diagrama fue eliminado', 'info');
                })
                .listen('.colaborador.agregado', (e) => {
                    console.log('Colaborador agregado via socket:', e);
                    // Mostramos una notificación más específica
                    showNotification(`'${e.colaboradorNombre}' fue agregado al diagrama.`, 'success');
                    // Despachamos un evento para que Livewire refresque la tabla
                    Livewire.dispatch('refrescar-tabla-diagramas', { diagramaId: e.diagramaId });
                })
                .listen('.colaborador.eliminado', (e) => {
                    console.log('Colaborador eliminado via socket:', e.colaboradorId);
                    showNotification('Se eliminó un colaborador', 'warning');
                });

            function showNotification(message, type = 'info') {
                // Implementar tu sistema de notificaciones
                if (typeof toastr !== 'undefined') {
                    toastr[type](message);
                } else {
                    // Notificación simple
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white ${
                        type === 'success' ? 'bg-green-500' : 
                        type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
                    }`;
                    notification.textContent = message;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => notification.remove(), 3000);
                }
            }
        });
    </script>
</x-app-layout>
