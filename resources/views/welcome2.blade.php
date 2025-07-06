<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Projeto Blade + Tailwind + jQuery</title>
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"
        integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- jQuery UI CSS CDN -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
    <style>
        /* Estilos extras para as listas e tasks */
        .lista {
            @apply bg-gray-100 rounded p-4 mb-6 min-h-[150px];
        }

        .card {
            @apply bg-white p-3 mb-2 rounded shadow cursor-move;
        }
    </style>
</head>

<body class="bg-gray-50 p-6 max-w-3xl mx-auto font-sans">

    <h1 class="text-3xl font-bold mb-4 text-center">Daily Tracke</h1>

    <!-- Botão que abre modal para adicionar card -->
    <div class="mb-6 text-center">
        <button id="btnAddCard" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Adicionar Card na
            TODO</button>
    </div>

    <div class="space-y-6">
        @php
            // dd($listas);
        @endphp
        @foreach ($listas as $lista)
            <section>
                <h2 class="text-xl font-semibold mb-3">{{ $lista }}</h2>
                <ul class="lista" id="lista-{{ strtolower(str_replace(' ', '-', $lista)) }}">
                    @php
                        // dd($tasks);
                    @endphp
                    @foreach ($tasks[$lista] ?? [] as $task)
                        @php
                            // dd($task);
                        @endphp
                        <li class="card">{{ $task['title'] }}</li>
                    @endforeach

                </ul>
            </section>
        @endforeach

    </div>

    <!-- Modal oculto -->
    <div id="modalAddCard" title="Adicionar Card" style="display:none;">
        <form id="formAddCard" method="POST" action="{{ route('tasks.store') }}">
            @csrf
            <div class="mb-4">
                <label for="title" class="block font-semibold mb-1">Título do Card</label>
                <input type="text" name="title" id="title" class="w-full border rounded px-2 py-1" required />
            </div>
            <input type="hidden" name="status" value="TODO" />
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Adicionar</button>
        </form>
    </div>

    <!-- jQuery e jQuery UI via CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        $(function() {
            $(".lista").sortable({
                connectWith: ".lista",
                placeholder: "bg-blue-200 border-2 border-blue-400 h-12 rounded mb-2",
                forcePlaceholderSize: true,
                tolerance: "pointer",
                cursor: "move"
            }).disableSelection();

            // Configura modal
            $("#modalAddCard").dialog({
                autoOpen: false,
                modal: true,
                width: 400,
            });

            // Botão abre modal
            $("#btnAddCard").click(function() {
                $("#modalAddCard").dialog("open");
            });
        });
    </script>
</body>

</html>
