<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diagrama;
use Illuminate\Support\Facades\Auth;
use App\Models\UsuarioDiagrama;
use App\Models\User;
use Livewire\Attributes\On;
use App\Events\ColaboradorAgregado;

class DiagramaTable extends Component
{
    public $diagramas;
    public $showModal = false;
    public $selectedDiagramId;
    public $users = [];
    public $tipoUsuario;
    public $usuariosSinRelacion = [];
    public $nombreDiagramaSeleccionado;

    // Escucha el evento despachado desde el frontend
    #[On('diagrama-eliminado-externamente')]
    public function onDiagramaEliminadoExternamente($diagramaId)
    {
        // Actualizar solo si el diagrama eliminado está en esta lista
        $this->diagramas = $this->diagramas->reject(function ($diagrama) use ($diagramaId) {
            return $diagrama->id == $diagramaId;
        });
        // Opcional: refrescar completamente si la lógica es compleja
        // $this->mount($this->tipoUsuario);
        session()->flash('livewire_message', 'Diagrama eliminado');
    }

    // Escucha el evento para refrescar la tabla cuando se agrega/elimina un colaborador externamente
    #[On('refrescar-tabla-diagramas')]
    public function refrescarTabla()
    {
        // Simplemente volvemos a ejecutar mount() para recargar todos los datos
        $this->mount($this->tipoUsuario);
    }

    public function mount($tipoUsuario = 'creador')
    {
        $this->tipoUsuario = $tipoUsuario;
        $this->diagramas = Diagrama::where('estado', true)
            ->whereHas('usuariosDiagrama', function ($query) {
                $query->where('user_id', Auth::id())
                    ->where('tipo_usuario', $this->tipoUsuario);
            })->get();
    }

    public function openModal($diagramId)
    {
        $this->selectedDiagramId = $diagramId;
        $diagrama = Diagrama::find($diagramId);
        $this->nombreDiagramaSeleccionado = $diagrama ? $diagrama->nombre : '';
        $this->loadUsersData();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDiagramId = null;
        $this->users = [];
        $this->usuariosSinRelacion = [];
        $this->nombreDiagramaSeleccionado = null;
    }

    public function redirectToDiagram($diagramId)
    {
        return redirect()->route('diagramas.show', $diagramId);
    }

    public function agregarUsuario($userId)
    {
        try {
            UsuarioDiagrama::create([
                'user_id' => $userId,
                'diagrama_id' => $this->selectedDiagramId,
                'actividad' => 'colaborador',
                'tipo_usuario' => 'colaborador',
                'estado' => true,
            ]);

            // 🔥 ¡ESTA ES LA PARTE QUE FALTABA! Disparar el evento para notificar a otros usuarios.
            $colaborador = User::find($userId);
            broadcast(new ColaboradorAgregado($this->selectedDiagramId, $userId, $colaborador->name))->toOthers();

            $this->loadUsersData(); // Recarga ambas listas para reflejar el cambio.
            session()->flash('message', 'Colaborador agregado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al agregar el colaborador.');
        }
    }

    public function eliminarUsuario($userId)
    {
        // 🔥 Lógica de Permisos: Solo los colaboradores pueden eliminarse a sí mismos.
        // El creador no puede eliminar a otros desde aquí.
        if ($this->tipoUsuario === 'creador') {
            session()->flash('error', 'Como creador, no puedes eliminar colaboradores desde esta lista.');
            return; // Detenemos la ejecución para que no se borre nada ni se recargue la lista.
        }

        // Si es un colaborador, solo puede eliminarse a sí mismo.
        if ($this->tipoUsuario === 'colaborador' && Auth::id() == $userId) {
            try {
                UsuarioDiagrama::where('user_id', $userId)
                    ->where('diagrama_id', $this->selectedDiagramId)
                    ->delete();

                // Refrescamos la tabla para que el diagrama desaparezca de la lista del colaborador.
                $this->mount($this->tipoUsuario);
                session()->flash('message', 'Has dejado de colaborar en el diagrama.');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al intentar dejar de colaborar.');
            }
        }
    }

    private function loadUsersData()
    {
        if (!$this->selectedDiagramId) {
            return;
        }

        // Obtiene usuarios que SÍ tienen relación con el diagrama.
        $this->users = User::whereHas('usuarioDiagrama', function ($query) {
            $query->where('diagrama_id', $this->selectedDiagramId);
        })->get()->reject(function ($user) {
            return $user->id === Auth::id();
        });

        // Obtiene usuarios que NO tienen relación con el diagrama.
        $this->usuariosSinRelacion = User::whereDoesntHave('usuarioDiagrama', function ($query) {
            $query->where('diagrama_id', $this->selectedDiagramId);
        })->get()->reject(function ($user) {
            return $user->id === Auth::id();
        });
    }

    public function render()
    {
        return view('livewire.diagrama-table');
    }
}
