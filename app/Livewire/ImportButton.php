<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Diagrama;
use App\Models\DiagramaReporte;
use App\Models\UsuarioDiagrama;

class ImportButton extends Component
{
    use WithFileUploads;

    public $file;
    public $resultado = '';
    public $isLoading = false;
    public $diagramaCreado = null; // 🔥 NUEVA propiedad para guardar el diagrama

    public function updatedFile()
    {
        try {
            // Validar el archivo subido
            $this->validate([
                'file' => 'file|mimes:jpg,jpeg,png,gif,webp|max:2048',
            ]);

            $this->isLoading = true;
            $this->resultado = '';
            $this->diagramaCreado = null; // Resetear

            Log::info('Procesando imagen de diagrama UML en ImportButton', [
                'file' => $this->file ? $this->file->getClientOriginalName() : 'No file'
            ]);

            // Obtener datos de la imagen
            $mimeType = $this->file->getMimeType();
            if (!in_array($mimeType, ['image/png', 'image/jpeg', 'image/webp'])) {
                throw new \Exception("Tipo de imagen no soportado ({$mimeType}). Use PNG, JPEG o WEBP.");
            }

            // Codificar la imagen en base64
            $imagePath = $this->file->getRealPath();
            if (!file_exists($imagePath)) {
                throw new \Exception('No se pudo acceder al archivo temporal.');
            }
            $imageBase64 = base64_encode(file_get_contents($imagePath));

            // Obtener la clave API
            $apiKey = env('GEMINI_API_KEY');
            if (!$apiKey) {
                throw new \Exception('La clave API de Gemini no está configurada en el archivo .env (GEMINI_API_KEY).');
            }

            // Prompt especializado para análisis de diagramas UML
            $promptText = $this->getUMLAnalysisPrompt();

            // Preparar el payload para la API de Gemini
            $payload = [
                'contents' => [[
                    'parts' => [
                        ['text' => $promptText],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $imageBase64
                            ]
                        ]
                    ]
                ]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 4096,
                    'response_mime_type' => 'application/json',
                ],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                    ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
                ]
            ];

            Log::info('Enviando solicitud a Gemini API para análisis UML');

            // Enviar solicitud a la API de Gemini
            $geminiModel = 'gemini-2.0-flash';
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$apiKey}";
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($apiUrl, $payload);

            // Verificar si la solicitud falló
            if ($response->failed()) {
                Log::error('Error en la respuesta de Gemini API', [
                    'status' => $response->status(), 
                    'body' => $response->body()
                ]);
                throw new \Exception('Error en la respuesta de Google Gemini API: Status ' . $response->status());
            }

            // Procesar la respuesta
            $data = $response->json();
            $jsonResponse = data_get($data, 'candidates.0.content.parts.0.text');

            if (is_null($jsonResponse)) {
                $finishReason = data_get($data, 'candidates.0.finishReason');
                Log::warning('No se obtuvo respuesta JSON de Gemini', ['finishReason' => $finishReason]);
                throw new \Exception('No se pudo obtener un análisis del diagrama UML.');
            }

            // Limpiar y validar el JSON
            $jsonResponse = $this->cleanJsonResponse($jsonResponse);
            $diagramData = json_decode($jsonResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON inválido recibido de Gemini', [
                    'raw_response' => $jsonResponse,
                    'error' => json_last_error_msg()
                ]);
                throw new \Exception('El análisis generado no es un JSON válido.');
            }

            // Validar estructura básica del diagrama
            if (!$this->validateDiagramStructure($diagramData)) {
                throw new \Exception('La estructura del diagrama generado no es válida.');
            }

            // 🔥 GUARDAR AUTOMÁTICAMENTE EN LA BASE DE DATOS
            $user = Auth::user();
            
            // Crear el diagrama en la base de datos
            $diagrama = Diagrama::create([
                'nombre' => 'Diagrama desde Imagen - ' . now()->format('d/m H:i'),
                'descripcion' => 'Diagrama UML generado automáticamente desde análisis de imagen',
                'contenido' => json_encode($diagramData, JSON_PRETTY_PRINT)
            ]);
            
            // Crear reporte inicial
            DiagramaReporte::crear($user->id, $diagrama->id, $diagramData);
            UsuarioDiagrama::crearRelacion($user->id, $diagrama->id, 'creado desde imagen', 'creador');

            // 🔥 GUARDAR EL DIAGRAMA CREADO PARA MOSTRAR EN LA VISTA
            $this->diagramaCreado = $diagrama;

            // Preparar mensaje de éxito
            $clasesCount = count($diagramData['nodeDataArray']);
            $relacionesCount = count($diagramData['linkDataArray']);
            
            $this->resultado = "✅ Diagrama UML analizado y guardado correctamente!\n\n" .
                              "📊 Resumen:\n" .
                              "• {$clasesCount} clases detectadas\n" .
                              "• {$relacionesCount} relaciones identificadas\n\n" .
                              "🔗 Haz clic en 'Abrir Editor' para comenzar a editar.";

            Log::info('Diagrama UML guardado en BD', [
                'diagrama_id' => $diagrama->id,
                'clases' => $clasesCount,
                'relaciones' => $relacionesCount
            ]);

        } catch (\Exception $e) {
            $this->resultado = "❌ Error al procesar el diagrama: " . $e->getMessage();
            Log::error('Error en análisis UML', [
                'message' => $e->getMessage(), 
                'file' => $e->getFile(), 
                'line' => $e->getLine()
            ]);
        }

        $this->isLoading = false;
    }

    // 🔥 NUEVO MÉTODO para redirigir al editor
    public function abrirEditor()
    {
        if ($this->diagramaCreado) {
            return redirect()->route('diagramas.show', $this->diagramaCreado);
        }
        
        $this->resultado = "❌ No hay diagrama disponible para abrir.";
        return null;
    }

    private function getUMLAnalysisPrompt(): string
    {
        return <<<PROMPT
Eres un experto en análisis de diagramas UML de clases. Analiza la imagen del diagrama y genera un JSON en el formato EXACTO requerido por GoJS GraphLinksModel.

FORMATO DE SALIDA REQUERIDO (JSON):
{
  "class": "GraphLinksModel",
  "copiesArrays": true,
  "copiesArrayObjects": true,
  "linkCategoryProperty": "relationship",
  "nodeDataArray": [
    {
      "key": "NombreClase1",
      "name": "NombreClase1",
      "stereotype": "", // Opcional: "interface", "abstract", etc.
      "properties": [
        {
          "name": "nombreAtributo",
          "type": "Tipo",
          "visibility": "public", // "public", "private", "protected"
          "default": "" // Valor por defecto opcional
        }
      ],
      "methods": [
        {
          "name": "nombreMetodo",
          "parameters": [
            {
              "name": "paramNombre",
              "type": "TipoParam"
            }
          ],
          "visibility": "public",
          "type": "TipoRetorno" // String, void, int, etc.
        }
      ]
    }
  ],
  "linkDataArray": [
    {
      "from": "ClaseOrigen",
      "to": "ClaseDestino",
      "relationship": "Association", // "Association", "Inheritance", "Composition", "Aggregation", "Dependency", "Realization"
      "multiplicityFrom": "1", // "1", "0..1", "0..*", "1..*", etc.
      "multiplicityTo": "0..*",
      "stereotype": "" // Opcional para relaciones
    }
  ]
}

INSTRUCCIONES CRÍTICAS:
1. Analiza TODOS los elementos visibles en el diagrama UML
2. Para cada clase, identifica: nombre, atributos (nombre, tipo, visibilidad) y métodos (nombre, parámetros, tipo retorno, visibilidad)
3. Para relaciones: identifica tipo (Asociación, Herencia, Composición, Agregación, Dependencia, Realización) y multiplicidades
4. Detecta estereotipos: <<interface>>, <<abstract>>, etc.
5. Usa claves únicas (key) simples basadas en nombres de clase
6. Si hay clases asociativas/intermedias, créalas como nodos normales
7. Para herencia, usa relationship: "Inheritance"
8. Para interfaces, usa stereotype: "interface" en el nodo

EJEMPLO DE RELACIONES:
- Herencia: {"from": "Hijo", "to": "Padre", "relationship": "Inheritance"}
- Asociación simple: {"from": "ClaseA", "to": "ClaseB", "relationship": "Association", "multiplicityFrom": "1", "multiplicityTo": "1"}
- Composición: {"from": "Contenedor", "to": "Contenido", "relationship": "Composition"}
- Agregación: {"from": "Contenedor", "to": "Contenido", "relationship": "Aggregation"}
- Dependencia: {"from": "Cliente", "to": "Servicio", "relationship": "Dependency"}
- Realización: {"from": "Implementacion", "to": "Interfaz", "relationship": "Realization"}

RESPONDE ÚNICAMENTE con el JSON válido, sin explicaciones adicionales.
PROMPT;
    }

    private function cleanJsonResponse(string $jsonResponse): string
    {
        $jsonResponse = preg_replace('/^```json|```$/m', '', $jsonResponse);
        $jsonResponse = trim($jsonResponse);
        $jsonResponse = preg_replace('/\s+/', ' ', $jsonResponse);
        return $jsonResponse;
    }

    private function validateDiagramStructure(array $diagramData): bool
    {
        if (!isset($diagramData['class']) || $diagramData['class'] !== 'GraphLinksModel') {
            return false;
        }
        
        if (!isset($diagramData['nodeDataArray']) || !is_array($diagramData['nodeDataArray'])) {
            return false;
        }
        
        if (!isset($diagramData['linkDataArray']) || !is_array($diagramData['linkDataArray'])) {
            return false;
        }
        
        foreach ($diagramData['nodeDataArray'] as $node) {
            if (!isset($node['key']) || !isset($node['name'])) {
                return false;
            }
        }
        
        foreach ($diagramData['linkDataArray'] as $link) {
            if (!isset($link['from']) || !isset($link['to']) || !isset($link['relationship'])) {
                return false;
            }
        }
        
        return true;
    }

    public function render()
    {
        return view('livewire.import-button');
    }
}