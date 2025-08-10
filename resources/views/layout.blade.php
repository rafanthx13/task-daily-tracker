<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Meu Projeto Laravel' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />

    <!-- Tailwind Utility Styles para listas e cards -->
    <style>

        .ui-widget-overlay {
    background: #000 !important; /* preto */
    opacity: 0.3 !important;     /* transparência de 30% */
}

/* overlay translúcido (substitui bg-black/bg-opacity-50) */
#editModal {
  background-color: rgba(0, 0, 0, 0.35) !important; /* 35% de escurecimento */
  display: flex;              /* já estava, mantemos */
  align-items: center;
  justify-content: center;
  /* z-index baixo só para garantir que modal filho fique acima, se necessário */
  z-index: 1000;
  transition: background-color 150ms ease;
}

/* garante que o painel branco interno fique acima do overlay e não herde opacidade */
#editModal > .bg-white,
#editModal .bg-white {
  position: relative;
  z-index: 1001;
}

/* opcional: estilo para o caso de a lista usar "hidden" via Tailwind */
#editModal.hidden {
  display: none !important;
}
        .lista {
            @apply bg-gray-100 rounded p-4 mb-6 min-h-[150px] border-2 border-gray-300;
        }

        .card {
            @apply bg-white p-3 mb-2 rounded shadow cursor-move border border-gray-400;
        }

        .ui-dialog-titlebar {
            @apply bg-blue-600 text-white text-lg font-semibold rounded-t px-4 py-2;
        }

        .ui-dialog-content {
            @apply p-4;
        }

        .ui-dialog-buttonpane {
            @apply px-4 pb-4;
        }

        .ui-dialog-buttonset button {
            @apply bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded;
        }

        .lista {
            min-height: 50px; /* altura mínima */
            border: 2px dashed #cbd5e0; /* opcional: borda para indicar área */
            padding: 0.5rem; /* espaço interno */
        }
    </style>

    @stack('head') <!-- Para adicionar coisas específicas por página -->
</head>

<body class="bg-gray-50 p-6 max-w-3xl mx-auto font-sans">
    <header class="mb-6 text-center">
        <h1 class="text-3xl font-bold mb-4">{{ $title ?? 'Daily Tracker' }} - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h1>
        <nav class="mb-4">
            <a href="{{ route('home') }}"
               class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Tarefas</a>
            <a href="{{ route('tags.index') }}"
               class="inline-block px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 ml-2">Tags</a>
        </nav>
    </header>

    @yield('content')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    @stack('scripts') <!-- Para scripts específicos -->

    <script>
            $(function() {
                $(".lista").sortable({
                    connectWith: ".lista",
                    placeholder: "bg-blue-200 border-2 border-blue-400 h-12 rounded mb-2",
                    forcePlaceholderSize: true,
                    tolerance: "pointer",
                    cursor: "move",
                    receive: function(event, ui) {
                        let taskId = ui.item.data('id'); // ID da task
                        let newStatus = $(this).attr('id').replace('lista-', '').toUpperCase(); // pega parte final do ID e converte

                        // Faz o update via AJAX
                        $.ajax({
                            url: `/tasks/change-lane/${taskId}`,
                            method: 'PUT',
                            data: {
                                _token: '{{ csrf_token() }}',
                                status: newStatus.toLowerCase()
                            },
                            success: function(response) {
                                console.log("Status atualizado:", response);
                            },
                            error: function(xhr) {
                                console.error("Erro ao atualizar status:", xhr.responseText);
                            }
                        });
                    }
                }).disableSelection();

                // Configura modal
                $("#modalAddCard").dialog({
                    autoOpen: false,
                    modal: true,
                    width: 400,
                });

                // Botão abre modal
                $("#btnAddCard").click(function() {
                    $("#modalEditCard").dialog({
    autoOpen: false,
    modal: true,   // mantém bloqueio
    width: 400
});
                });

                $(document).on('click', '.edit-task', function() {
        let card = $(this).closest('.card');
        let id = card.data('id');
        let title = card.find('h3').text();
        let notes = card.find('p').text();

        $('#editTaskId').val(id);
        $('#editTaskTitle').val(title);
        $('#editTaskNotes').val(notes);

        $('#editModal').removeClass('hidden');
    });

    // Fechar modal
    $('#closeModal').click(function() {
        $('#editModal').addClass('hidden');
    });

    // Salvar alterações
    $('#editTaskForm').submit(function(e) {
        e.preventDefault();

        let id = $('#editTaskId').val();
        let title = $('#editTaskTitle').val();
        let notes = $('#editTaskNotes').val();

        $.ajax({
            url: '/tasks/' + id,
            method: 'PUT',
            data: {
                title: title,
                notes: notes,
                _token: '{{ csrf_token() }}'
            },
            success: function() {
                location.reload(); // ou atualizar só o card editado
            }
        });
    });




            });
        </script>
</body>
</html>
