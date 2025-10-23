<?php

namespace App\Livewire;

use App\Models\Diagrama;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;


class CreateDiagram extends Component
{
    public $showModal = false; // control del modal

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.create-diagram');
    }
}
