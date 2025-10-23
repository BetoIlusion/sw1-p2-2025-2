<?php

namespace App\Http\Controllers;

use App\Models\Diagrama;

abstract class Controller
{
    public function index(){
        $diagrama = Diagrama::where('estado', true)->get();

        return view('dashboard1',
        [
            'diagrama' => $diagrama
        ]);
    
    }
    public function compartidos(){



    }
}
