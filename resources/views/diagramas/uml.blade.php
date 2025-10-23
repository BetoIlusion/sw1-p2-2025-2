<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UML Class Diagram</title>
    <!-- Include GoJS library -->
    <script src="https://unpkg.com/gojs@2.3.18/release/go.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }

        #container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 800px;
        }

        #leftPanel {
            width: 100%;
            height: 80vh;
            border: 1px solid black;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: white;
        }

        #myDiagramDiv {
            width: 100%;
            height: calc(100% - 50px);
            /* Space for button */
            border: 1px solid black;
            flex-grow: 1;
        }

        #buttonPanel {
            padding: 10px;
            text-align: center;
            width: 100%;
        }
    </style>
</head>

<body>
    <div id="container">
        <div id="leftPanel">
            <div id="buttonPanel">
                <button onclick="addNewClass()">Add New Class</button>
                {{-- <button onclick="location.href='{{ route('diagramas.exportarSpringBoot', ['id' => $id]) }}'">Export to Spring Boot</button> --}}
            </div>
            {{-- Panel para la IA --}}
            <div id="aiPanel" style="padding: 10px; text-align: center; width: 100%; border-top: 1px solid black;">
                <h3 style="margin-bottom: 5px;">Asistente IA</h3>
                <input type="text" id="aiPrompt" placeholder="Ej: Agrega una clase 'Producto' con id y nombre"
                    style="width: 70%; padding: 5px;">
                <button onclick="updateWithAI()" style="padding: 5px;">Generar</button>
            </div>
            <div id="myDiagramDiv"></div>
        </div>
    </div>

    <script>
        // Initialize GoJS diagram
        function init() {
            // Access the diagram model passed from Laravel
            var jsonInicial = @json($jsonInicial);
            var diagramaId = @json($diagramaId);

            var linkingMode = false;
            var sourceNode = null;
            var linkingTool = null;

            // Initialize the diagram
            var myDiagram = new go.Diagram('myDiagramDiv', {
                'undoManager.isEnabled': true,
                layout: new go.TreeLayout({
                    angle: 90,
                    path: go.TreePath.Source,
                    setsPortSpot: false,
                    setsChildPortSpot: false,
                    arrangement: go.TreeArrangement.Horizontal
                })
            });


            // Convert visibility to symbols
            function convertVisibility(v) {
                switch (v) {
                    case 'public':
                        return '+';
                    case 'private':
                        return '-';
                    case 'protected':
                        return '#';
                    case 'package':
                        return '~';
                    default:
                        return v;
                }
            }
            // Reemplazar donde se define propertyTemplate
            var propertyTemplate = new go.Panel('Horizontal')
                .add(
                    // Botón para eliminar la propiedad
                    go.GraphObject.build("Button", {
                        margin: 2,
                        width: 20,
                        height: 20,
                        click: function(e, button) {
                            var panel = button.panel; // El panel Horizontal que contiene el botón
                            var node = panel.part; // El nodo que contiene este panel
                            var propertyData = panel.data; // Los datos de la propiedad

                            e.diagram.startTransaction("removeProperty");
                            var properties = node.data.properties;
                            var index = properties.indexOf(propertyData);
                            if (index !== -1) {
                                e.diagram.model.removeArrayItem(properties, index);
                            }
                            e.diagram.commitTransaction("removeProperty");
                        }
                    })
                    .add(
                        new go.TextBlock("X", {
                            font: "10pt sans-serif"
                        })
                    ),
                    // Resto del template de propiedades
                    new go.TextBlock({
                        isMultiline: false,
                        editable: false,
                        width: 12
                    })
                    .bind('text', 'visibility', convertVisibility),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'name')
                    .bind('isUnderline', 'scope', s => s[0] === 'c'),
                    new go.TextBlock('')
                    .bind('text', 'type', t => t ? ': ' : ''),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'type'),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: false
                    })
                    .bind('text', 'default', s => s ? ' = ' + s : '')
                );

            var methodTemplate = new go.Panel('Horizontal')
                .add(
                    // Botón para eliminar el método
                    go.GraphObject.build("Button", {
                        margin: 2,
                        width: 20,
                        height: 20,
                        click: function(e, button) {
                            var panel = button.panel; // El panel Horizontal que contiene el botón
                            var node = panel.part; // El nodo que contiene este panel
                            var methodData = panel.data; // Los datos del método

                            e.diagram.startTransaction("removeMethod");
                            var methods = node.data.methods;
                            var index = methods.indexOf(methodData);
                            if (index !== -1) {
                                e.diagram.model.removeArrayItem(methods, index);
                            }
                            e.diagram.commitTransaction("removeMethod");
                        }
                    })
                    .add(
                        new go.TextBlock("X", {
                            font: "10pt sans-serif"
                        })
                    ),
                    // Resto del template de métodos
                    new go.TextBlock({
                        isMultiline: false,
                        editable: false,
                        width: 12
                    })
                    .bind('text', 'visibility', convertVisibility),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'name')
                    .bind('isUnderline', 'scope', s => s[0] === 'c'),
                    new go.TextBlock('()')
                    .bind('text', 'parameters', parr => {
                        var s = '(';
                        for (var i = 0; i < parr.length; i++) {
                            var param = parr[i];
                            if (i > 0) s += ', ';
                            s += param.name + ': ' + param.type;
                        }
                        return s + ')';
                    }),
                    new go.TextBlock('')
                    .bind('text', 'type', t => t ? ': ' : ''),
                    new go.TextBlock({
                        isMultiline: false,
                        editable: true
                    })
                    .bindTwoWay('text', 'type')
                );
            // Node template
            myDiagram.nodeTemplate = new go.Node('Auto', {
                    locationSpot: go.Spot.Center,
                    fromSpot: go.Spot.AllSides,
                    toSpot: go.Spot.AllSides
                })
                //.bind(new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify))
                .add(
                    new go.Shape({
                        fill: 'lightyellow'
                    }),
                    new go.Panel('Table', {
                        defaultRowSeparatorStroke: 'black'
                    })
                    .add(
                        new go.Panel("Vertical", {
                            row: 0,
                            columnSpan: 2,
                            margin: 3,
                            alignment: go.Spot.Center
                        })
                        .add(
                            new go.TextBlock({
                                font: "10pt sans-serif",
                                isMultiline: false,
                                editable: true
                            })
                            .bindTwoWay("text", "stereotype", s => s ? `<<${s}>>` : ""),
                            new go.TextBlock({
                                font: "bold 12pt sans-serif",
                                isMultiline: false,
                                editable: true
                            })
                            .bindTwoWay("text", "name")
                        ),
                        new go.TextBlock('Properties', {
                            row: 1,
                            font: 'italic 10pt sans-serif'
                        })
                        .bindObject('visible', 'visible', v => !v, undefined, 'PROPERTIES'),
                        new go.Panel('Vertical', {
                            name: 'PROPERTIES',
                            row: 1,
                            margin: 3,
                            stretch: go.Stretch.Horizontal,
                            defaultAlignment: go.Spot.Left,
                            background: 'lightyellow',
                            itemTemplate: propertyTemplate
                        })
                        .bind('itemArray', 'properties'),
                        go.GraphObject.build("PanelExpanderButton", {
                            row: 1,
                            column: 1,
                            alignment: go.Spot.TopRight,
                            visible: false
                        }, "PROPERTIES")
                        .bind('visible', 'properties', arr => arr.length > 0),
                        new go.TextBlock('Methods', {
                            row: 2,
                            font: 'italic 10pt sans-serif'
                        })
                        .bindObject('visible', 'visible', v => !v, undefined, 'METHODS'),
                        new go.Panel('Vertical', {
                            name: 'METHODS',
                            row: 2,
                            margin: 3,
                            stretch: go.Stretch.Horizontal,
                            defaultAlignment: go.Spot.Left,
                            background: 'lightyellow',
                            itemTemplate: methodTemplate
                        })
                        .bind('itemArray', 'methods'),
                        go.GraphObject.build("PanelExpanderButton", {
                            row: 2,
                            column: 1,
                            alignment: go.Spot.TopRight,
                            visible: false
                        }, "METHODS")
                        .bind('visible', 'methods', arr => arr.length > 0)
                    )
                );


            // Link style
            function linkStyle() {
                return {
                    isTreeLink: false,
                    fromEndSegmentLength: 0,
                    toEndSegmentLength: 0
                };
            }

            function generateDiagramOutput() {
                try {
                    var currentDiagramJson = myDiagram.model.toJson();

                    console.log('Enviando diagrama...', {
                        diagramaId: diagramaId,
                        dataLength: currentDiagramJson.length
                    });

                    var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                    var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

                    if (!csrfToken) {
                        console.error('No se encontró el token CSRF');
                        return;
                    }

                    fetch("{{ route('diagrama-reporte.create') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({
                                diagramData: currentDiagramJson,
                                diagramaId: diagramaId
                            })
                        })
                        .then(response => {
                            console.log('Respuesta recibida:', response.status, response.statusText);

                            if (!response.ok) {
                                return response.text().then(text => {
                                    console.error('Error detallado:', text);
                                    throw new Error('HTTP ' + response.status + ' - ' + text);
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Diagrama guardado correctamente:', data);
                        })
                        .catch(error => {
                            console.error('Error completo al guardar:', error);
                        });

                } catch (error) {
                    console.error('Error en generateDiagramOutput:', error);
                }
            };


            myDiagram.linkTemplate =
                new go.Link({
                    routing: go.Link.AvoidsNodes,
                    curve: go.Link.None,
                    selectable: true,
                    selectionAdorned: true
                })
                .add(
                    // línea del enlace
                    new go.Shape({
                        strokeWidth: 1
                    })
                    .bind(new go.Binding("strokeWidth", "strokeWidth")),

                    // arrowhead para el extremo "to" — sólo visible si el linkData tiene toArrow no vacío
                    new go.Shape({
                        // no rellenar ni trazar por defecto (evita rectángulos visibles)
                        fill: null,
                        stroke: null
                    })
                    .bind(new go.Binding("toArrow")) // enlaza el tipo de flecha si existe
                    .bind(new go.Binding("visible", "toArrow", function(v) {
                        return !!v;
                    })),

                    // arrowhead para el extremo "from" — sólo visible si el linkData tiene fromArrow no vacío
                    new go.Shape({
                        fill: null,
                        stroke: null
                    })
                    .bind(new go.Binding("fromArrow"))
                    .bind(new go.Binding("visible", "fromArrow", function(v) {
                        return !!v;
                    })),

                    // multiplicidad en origen (sin panel que dibuje fondo)
                    new go.TextBlock({
                        segmentIndex: 0,
                        segmentFraction: 0,
                        segmentOffset: new go.Point(-10, -10),
                        background: null,
                        editable: false
                    }).bind(new go.Binding("text", "multiplicityFrom")),

                    // multiplicidad en destino (sin panel que dibuje fondo)
                    new go.TextBlock({
                        segmentIndex: -1,
                        segmentFraction: 1,
                        segmentOffset: new go.Point(10, -10),
                        background: null,
                        editable: false
                    }).bind(new go.Binding("text", "multiplicityTo"))
                );



            myDiagram.linkTemplateMap.add('Association',
                new go.Link(linkStyle())
                .add(new go.Shape()),
            );

            myDiagram.linkTemplateMap.add('Realization',
                new go.Link(linkStyle())
                .add(
                    new go.Shape({
                        strokeDashArray: [3, 2]
                    }),
                    new go.Shape().bind(new go.Binding("toArrow")).bind(new go.Binding("fill"))
                ));


            myDiagram.linkTemplateMap.add('Dependency',
                new go.Link(linkStyle())
                .add(
                    new go.Shape({
                        strokeDashArray: [3, 2]
                    }),
                    new go.Shape({
                        toArrow: 'OpenTriangle'
                    })
                ));

            myDiagram.linkTemplateMap.add('Composition',
                new go.Link(linkStyle())
                .add(
                    new go.Shape(),
                    new go.Shape({
                        fromArrow: 'StretchedDiamond',
                        scale: 1.3
                    }),
                    new go.Shape({
                        toArrow: 'OpenTriangle'
                    })
                ));

            myDiagram.linkTemplateMap.add('Aggregation',
                new go.Link(linkStyle())
                .add(
                    new go.Shape(),
                    new go.Shape({
                        fromArrow: 'StretchedDiamond',
                        fill: 'white',
                        scale: 1.3
                    }),
                    new go.Shape({
                        toArrow: 'OpenTriangle'
                    })
                ));

            // Cargar el modelo desde el JSON inicial
            myDiagram.model = go.GraphLinksModel.fromJson(jsonInicial);
            // Asegurar configuraciones necesarias
            myDiagram.model.copiesArrays = true;
            myDiagram.model.copiesArrayObjects = true;
            myDiagram.model.linkCategoryProperty = 'relationship';
            // myDiagram.model = go.Model.fromJson(jsonInicial);
            // // Set the model from Laravel
            // myDiagram.model = new go.GraphLinksModel({
            //     copiesArrays: true,
            //     copiesArrayObjects: true,
            //     linkCategoryProperty: 'relationship',
            //     ...jsonInicial
            // });
            // REEMPLAZAR: todo myDiagram.linkTemplate por este bloque
            // Agregar listener para cambios en el modelo
            myDiagram.model.addChangedListener(function(e) {
                // Esperar a que termine la transacción para evitar múltiples llamadas
                if (e.isTransactionFinished) {
                    generateDiagramOutput();
                }
            });
            // Definir el menú contextual
            var contextMenu = new go.Adornment("Vertical")
                .add(
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Añadir Atributo", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var node = e.diagram.selection.first();
                            if (node) {
                                e.diagram.startTransaction("addAttribute");
                                var newAttr = {
                                    name: "newAttr",
                                    type: "String",
                                    visibility: "public"
                                };
                                var data = node.data;
                                e.diagram.model.addArrayItem(data.properties, newAttr);
                                e.diagram.commitTransaction("addAttribute");
                            }
                        }
                    }),
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Añadir Operación", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var node = e.diagram.selection.first();
                            if (node) {
                                e.diagram.startTransaction("addOperation");
                                var newOp = {
                                    name: "newOperation",
                                    parameters: [],
                                    visibility: "public",
                                    type: ""
                                };
                                var data = node.data;
                                e.diagram.model.addArrayItem(data.methods, newOp);
                                e.diagram.commitTransaction("addOperation");
                            }
                        }
                    }),
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Añadir Estereotipo", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var node = e.diagram.selection.first();
                            if (node) {
                                e.diagram.startTransaction("addStereotype");
                                e.diagram.model.setDataProperty(node.data, "stereotype", "stereotype");
                                e.diagram.commitTransaction("addStereotype");
                            }
                        }
                    }),
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Crear Composición", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var fromNode = myDiagram.selection.first();
                            if (!fromNode) return;

                            var fromKey = fromNode.data.key;
                            myDiagram.select(fromNode);

                            var compHandler = function(ev) {
                                var clickedPart = ev.subject.part;
                                if (!clickedPart) return;
                                if (clickedPart.data && clickedPart.data.key === fromKey) return;

                                var toKey = clickedPart.data.key;

                                myDiagram.startTransaction("createComposition");
                                myDiagram.model.addLinkData({
                                    from: fromKey,
                                    to: toKey,
                                    relationship: "Composition",
                                    multiplicityFrom: "1",
                                    multiplicityTo: "0..*"
                                });
                                myDiagram.commitTransaction("createComposition");

                                myDiagram.removeDiagramListener("ObjectSingleClicked", compHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            var bgCancelHandler = function(ev) {
                                myDiagram.removeDiagramListener("ObjectSingleClicked", compHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            myDiagram.addDiagramListener("ObjectSingleClicked", compHandler);
                            myDiagram.addDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                        }
                    }),
                    // Botón para Agregación
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Crear Agregación", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var fromNode = myDiagram.selection.first();
                            if (!fromNode) return;

                            var fromKey = fromNode.data.key;
                            myDiagram.select(fromNode);

                            var aggHandler = function(ev) {
                                var clickedPart = ev.subject.part;
                                if (!clickedPart) return;
                                if (clickedPart.data && clickedPart.data.key === fromKey) return;

                                var toKey = clickedPart.data.key;

                                myDiagram.startTransaction("createAggregation");
                                myDiagram.model.addLinkData({
                                    from: fromKey,
                                    to: toKey,
                                    relationship: "Aggregation",
                                    multiplicityFrom: "1",
                                    multiplicityTo: "0..*"
                                });
                                myDiagram.commitTransaction("createAggregation");

                                myDiagram.removeDiagramListener("ObjectSingleClicked", aggHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            var bgCancelHandler = function(ev) {
                                myDiagram.removeDiagramListener("ObjectSingleClicked", aggHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            myDiagram.addDiagramListener("ObjectSingleClicked", aggHandler);
                            myDiagram.addDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                        }
                    }),

                    // Botón para Dependencia
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Crear Dependencia", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var fromNode = myDiagram.selection.first();
                            if (!fromNode) return;

                            var fromKey = fromNode.data.key;
                            myDiagram.select(fromNode);

                            var depHandler = function(ev) {
                                var clickedPart = ev.subject.part;
                                if (!clickedPart) return;
                                if (clickedPart.data && clickedPart.data.key === fromKey) return;

                                var toKey = clickedPart.data.key;

                                myDiagram.startTransaction("createDependency");
                                myDiagram.model.addLinkData({
                                    from: fromKey,
                                    to: toKey,
                                    relationship: "Dependency",
                                    multiplicityFrom: "",
                                    multiplicityTo: ""
                                });
                                myDiagram.commitTransaction("createDependency");

                                myDiagram.removeDiagramListener("ObjectSingleClicked", depHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            var bgCancelHandler = function(ev) {
                                myDiagram.removeDiagramListener("ObjectSingleClicked", depHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            myDiagram.addDiagramListener("ObjectSingleClicked", depHandler);
                            myDiagram.addDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                        }
                    }),
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Crear Tabla Intermedia", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var firstClass = myDiagram.selection.first();
                            if (!firstClass) return;

                            var firstKey = firstClass.data.key;
                            var firstLoc = firstClass.location;
                            myDiagram.select(firstClass);

                            var secondClassHandler = function(ev) {
                                var clickedPart = ev.subject.part;
                                if (!clickedPart || !(clickedPart instanceof go.Node)) return;
                                if (clickedPart.data.key === firstKey) return;

                                var secondKey = clickedPart.data.key;
                                var secondLoc = clickedPart.location;
                                var diagram = ev.diagram;

                                diagram.startTransaction("createIntermediateTable");

                                // Calcular posición intermedia
                                var midX = (firstLoc.x + secondLoc.x) / 2;
                                var midY = (firstLoc.y + secondLoc.y) / 2;

                                // Crear la clase intermedia
                                var intermediateClassKey = diagram.model.nodeDataArray.length + 1;
                                var intermediateClass = {
                                    key: intermediateClassKey,
                                    name: firstClass.data.name + "_" + clickedPart.data.name,
                                    properties: [],
                                    methods: [],
                                    loc: go.Point.stringify(new go.Point(midX, midY))
                                };
                                diagram.model.addNodeData(intermediateClass);

                                // Crear asociaciones en forma de T
                                diagram.model.addLinkData({
                                    from: firstKey,
                                    to: intermediateClassKey,
                                    relationship: "Association",
                                    multiplicityFrom: "1",
                                    multiplicityTo: "1"
                                });

                                diagram.model.addLinkData({
                                    from: secondKey,
                                    to: intermediateClassKey,
                                    relationship: "Association",
                                    multiplicityFrom: "1",
                                    multiplicityTo: "1"
                                });

                                diagram.commitTransaction("createIntermediateTable");

                                // Remover listeners
                                diagram.removeDiagramListener("ObjectSingleClicked", secondClassHandler);
                                diagram.removeDiagramListener("BackgroundSingleClicked", cancelHandler);
                            };

                            var cancelHandler = function(ev) {
                                var diagram = ev.diagram;
                                diagram.removeDiagramListener("ObjectSingleClicked", secondClassHandler);
                                diagram.removeDiagramListener("BackgroundSingleClicked", cancelHandler);
                            };

                            myDiagram.addDiagramListener("ObjectSingleClicked", secondClassHandler);
                            myDiagram.addDiagramListener("BackgroundSingleClicked", cancelHandler);
                        }
                    }),
                    // new go.Panel("Auto", {
                    //     margin: 2
                    // })
                    // .add(
                    //     new go.Shape({
                    //         fill: "white",
                    //         stroke: "gray",
                    //         width: 120,
                    //         height: 30
                    //     }),
                    //     new go.TextBlock("Ctar. Asociacion", {
                    //         margin: 5
                    //     })
                    // )
                    // .set({
                    //     click: function(e, obj) {
                    //         var diagram = e.diagram;
                    //         var fromNode = diagram.selection.first();
                    //         if (!fromNode) return;

                    //         var fromKey = fromNode.data.key;
                    //         // indicador temporal (opcional): seleccionar la fuente para que el usuario la vea
                    //         diagram.select(fromNode);

                    //         // handler cuando el usuario haga click en otro nodo (destino)
                    //         var assocHandler = function(ev) {
                    //             var clickedPart = ev.subject.part;
                    //             if (!clickedPart) return;
                    //             // ignorar si es el mismo nodo
                    //             if (clickedPart.data && clickedPart.data.key === fromKey) return;

                    //             var toKey = clickedPart.data.key;

                    //             // crear el link en el modelo (ajusta propiedades según tu modelo)
                    //             diagram.startTransaction("createAssociation");
                    //             // usa 'from' y 'to' si tu GraphLinksModel lo espera (ajustar si tu modelo usa otros nombres)
                    //             diagram.model.addLinkData({
                    //                 from: fromKey,
                    //                 to: toKey,
                    //                 relationship: "association",
                    //                 multiplicityFrom: "", // dejar vacío o poner "1" / "0..*" etc.
                    //                 multiplicityTo: ""
                    //             });
                    //             diagram.commitTransaction("createAssociation");

                    //             // limpiar: remover listeners
                    //             diagram.removeDiagramListener("ObjectSingleClicked", assocHandler);
                    //             diagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                    //         };

                    //         // handler para cancelar si hace clic en fondo
                    //         var bgCancelHandler = function(ev) {
                    //             // limpiar sin crear nada
                    //             diagram.removeDiagramListener("ObjectSingleClicked", assocHandler);
                    //             diagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                    //         };

                    //         // registrar listeners temporales: esperar al click en la segunda clase o en el fondo
                    //         diagram.addDiagramListener("ObjectSingleClicked", assocHandler);
                    //         diagram.addDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                    //     }
                    // }),
                    new go.Panel("Auto", {
                        margin: 2
                    })
                    .add(
                        new go.Shape({
                            fill: "white",
                            stroke: "gray",
                            width: 120,
                            height: 30
                        }),
                        new go.TextBlock("Crear Relación", {
                            margin: 5
                        })
                    )
                    .set({
                        click: function(e, obj) {
                            var fromNode = myDiagram.selection.first();
                            if (!fromNode) return;

                            var fromKey = fromNode.data.key;
                            myDiagram.select(fromNode);

                            var relHandler = function(ev) {
                                var clickedPart = ev.subject.part;
                                if (!clickedPart) return;
                                if (clickedPart.data && clickedPart.data.key === fromKey) return;

                                var toKey = clickedPart.data.key;

                                myDiagram.startTransaction("createRelationSimple");
                                myDiagram.model.addLinkData({
                                    from: fromKey,
                                    to: toKey,
                                    relationship: "association",
                                    multiplicityFrom: "1",
                                    multiplicityTo: "0..*",
                                    stereotype: ""
                                });
                                myDiagram.commitTransaction("createRelationSimple");

                                myDiagram.removeDiagramListener("ObjectSingleClicked", relHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            var bgCancelHandler = function(ev) {
                                myDiagram.removeDiagramListener("ObjectSingleClicked", relHandler);
                                myDiagram.removeDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                            };

                            myDiagram.addDiagramListener("ObjectSingleClicked", relHandler);
                            myDiagram.addDiagramListener("BackgroundSingleClicked", bgCancelHandler);
                        }
                    }),
                );
            myDiagram.nodeTemplate.contextMenu = contextMenu;

            // --- AÑADIR/REEMPLAZAR: editar multiplicidades en doble clic usando myDiagram
            myDiagram.addDiagramListener("ObjectDoubleClicked", function(e) {
                var part = e.subject.part;
                if (part instanceof go.Link) {
                    var link = part;
                    var currentFrom = link.data.multiplicityFrom || "";
                    var currentTo = link.data.multiplicityTo || "";

                    var newFrom = window.prompt("Multiplicidad (origen):", currentFrom);
                    if (newFrom === null) return;
                    var newTo = window.prompt("Multiplicidad (destino):", currentTo);
                    if (newTo === null) return;

                    myDiagram.startTransaction("editMultiplicities");
                    myDiagram.model.setDataProperty(link.data, "multiplicityFrom", newFrom);
                    myDiagram.model.setDataProperty(link.data, "multiplicityTo", newTo);
                    myDiagram.commitTransaction("editMultiplicities");
                }
            });



            // Function to add a new class
            window.addNewClass = function() {
                myDiagram.startTransaction('addClass');
                var newClass = {
                    key: myDiagram.model.nodeDataArray.length + 1,
                    name: 'Class' + (myDiagram.model.nodeDataArray.length + 1),
                    properties: [],
                    methods: []
                };
                myDiagram.model.addNodeData(newClass);
                myDiagram.commitTransaction('addClass');
            };
            // Añadir un rebuild para refrescar nodos existentes (esto fuerza la aplicación del template a nodos iniciales)
            myDiagram.rebuildParts();

            // 🔥 ¡AQUÍ ESTÁ LA CORRECCIÓN! Llamamos a la función para escuchar eventos.
            setupEchoListener(myDiagram, diagramaId);
        }

        // Función para actualizar el diagrama con IA
        function updateWithAI() {
            var prompt = document.getElementById('aiPrompt').value;
            if (!prompt) {
                alert('Por favor, introduce una instrucción para la IA.');
                return;
            }

            var myDiagram = go.Diagram.fromDiv('myDiagramDiv');
            var currentDiagramJson = myDiagram.model.toJson();
            var diagramaId = @json($diagramaId);

            console.log('Enviando prompt a la IA...', {
                diagramaId: diagramaId,
                prompt: prompt
            });

            var csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            var csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;

            if (!csrfToken) {
                console.error('No se encontró el token CSRF');
                return;
            }

            // Muestra un indicador de carga (opcional)
            document.getElementById('aiPrompt').disabled = true;
            document.querySelector('#aiPanel button').disabled = true;
            document.querySelector('#aiPanel button').textContent = 'Generando...';

            fetch("{{ route('diagramas.updateWithAI', ['diagrama' => $diagramaId]) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        diagramData: currentDiagramJson,
                        diagramaId: diagramaId,
                        prompt: prompt
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    console.log('Diagrama actualizado por la IA:', data.updatedDiagram);
                    // Actualizar el modelo del diagrama GoJS con el nuevo JSON
                    myDiagram.model = go.GraphLinksModel.fromJson(data.updatedDiagram);
                    // Recargar la página para "redirigir" a la misma vista y sincronizar
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error al actualizar con IA:', error);
                    alert('Ocurrió un error al procesar la solicitud de IA: ' + error.message);
                }).finally(() => {
                    // Restablece el UI
                    document.getElementById('aiPrompt').disabled = false;
                    document.querySelector('#aiPanel button').disabled = false;
                    document.querySelector('#aiPanel button').textContent = 'Generar';
                });
        }

        // Una vez que 'init' se ha ejecutado y el diagrama está listo, configuramos Echo.
        function setupEchoListener(diagram, diagramId) {
            if (diagramId) {
                window.Echo.private(`diagrama.${diagramaId}`)
                    .listen('.diagrama.actualizado', (e) => {
                        console.log('Diagrama actualizado recibido vía Reverb:', e);

                        // 🔥 SOLUCIÓN SIMPLE: Recargar la página para ver los cambios.
                        // El método toOthers() en el controlador asegura que esta recarga
                        // solo ocurra en los navegadores de los otros colaboradores.
                        console.log('Forzando recarga de la página para sincronizar cambios.');
                        window.location.reload();
                    });
            }
        }

        // Initialize when DOM is loaded
        window.addEventListener('DOMContentLoaded', function() {
            // Definimos la función que maneja los cambios para poder añadirla y quitarla
            window.onDiagramChanged = function(e) {
                if (e.isTransactionFinished) {
                    generateDiagramOutput();
                }
            };
            init(); // Llamamos a init después de definir la función global
        });
    </script>

</body>

</html>
