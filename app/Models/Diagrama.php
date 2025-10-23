<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UsuarioDiagrama;
use Illuminate\Support\Facades\Auth;


class Diagrama extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'descripcion',
        'contenido',
        'estado',
    ];

    // protected $hidden = [
    //     'contenido',
    // ];

    // protected $appends = [
    //     'contenido',
    // ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    // protected function casts(): array
    // {
    //     return [
    //         'contenido' => 'json',
    //     ];
    // }



    public static function diagramaInicial(): array
    {
        return [
            "class" => "GraphLinksModel",
            "copiesArrays" => true,
            "copiesArrayObjects" => true,
            "linkCategoryProperty" => "relationship",
            "nodeDataArray" => [
                [
                    "key" => "NewClass",
                    "name" => "NewClass",
                    "properties" => [
                        ["name" => "exampleProperty", "type" => "String", "visibility" => "public"]
                    ],
                    "methods" => [
                        ["name" => "exampleMethod", "parameters" => [["name" => "param", "type" => "int"]], "visibility" => "public"]
                    ]
                ],
                [
                    "key" => "NewClass2",
                    "name" => "NewClass2",
                    "properties" => [],
                    "methods" => []
                ]
            ],
            "linkDataArray" => [
                [
                    "from" => "NewClass",
                    "to" => "NewClass2",
                    "relationship" => "Association Simple",
                    "fromCardinality" => "1..1",
                    "toCardinality" => "1..*"
                ]
            ]
        ];
    }
    public function usuariosDiagrama()
    {
        return $this->hasMany(UsuarioDiagrama::class);
    }   

    public function reportes()
    {
        return $this->hasMany(DiagramaReporte::class, 'diagrama_id');
    }
}
