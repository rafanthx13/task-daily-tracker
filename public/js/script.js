$(function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $(".lista")
        .sortable({
            connectWith: ".lista",
            placeholder: "bg-blue-200 border-2 border-blue-400 h-12 rounded mb-2",
            forcePlaceholderSize: true,
            tolerance: "pointer",
            cursor: "move",
            receive: function (event, ui) {
                let taskId = ui.item.data("id"); // ID da task
                let newStatus = $(this)
                    .attr("id")
                    .replace("lista-", "")
                    .toUpperCase(); // pega parte final do ID e converte

                // Faz o update via AJAX
                $.ajax({
                    url: `/tasks/change-lane/${taskId}`,
                    method: "PUT",
                    data: {
                        _token: csrfToken,
                        status: newStatus.toLowerCase(),
                    },
                    success: function (response) {
                        console.log("Status atualizado:", response);
                    },
                    error: function (xhr) {
                        console.error(
                            "Erro ao atualizar status:",
                            xhr.responseText
                        );
                    },
                });
            },
        })
        .disableSelection();

    // Configura modal
    $("#modalAddCard").dialog({
        autoOpen: false,
        modal: true,
        width: "400",
        draggable: false,
        position: { my: "center", at: "center", of: window },
    });

    // Botão abre modal
    $("#btnAddCard").on("click", function () {
        $("#modalAddCard").dialog("open");
    });

    // Abrir modal de Editar Task
    $(document).on("click", ".edit-task", function () {
        let card = $(this).closest(".card");
        let id = card.data("id");
        let title = card.find("h3").text();
        let notes = card.find("p").text();

        $("#editTaskId").val(id);
        $("#editTaskTitle").val(title);
        $("#editTaskNotes").val(notes);

        $("#editModal").removeClass("hidden");
    });

    // Fechar modal de Editar Task
    $("#closeModal").click(function () {
        $("#editModal").addClass("hidden");
    });

    // Ação de Editar Task
    $("#editTaskForm").submit(function (e) {
        e.preventDefault();

        let id = $("#editTaskId").val();
        let title = $("#editTaskTitle").val();
        let notes = $("#editTaskNotes").val();

        $.ajax({
            url: "/tasks/" + id,
            method: "PUT",
            data: {
                title: title,
                notes: notes,
                _token: csrfToken,
            },
            success: function () {
                location.reload(); // ou atualizar só o card editado
            },
        });
    });

    // Deletar Task
    $("#deleteTaskForm").click(function (e) {
        e.preventDefault();


        let id = $("#editTaskId").val();

        $.ajax({
            url: "/tasks/" + id,
            method: "DELETE",
            data: {
                _token: csrfToken,
            },
            success: function () {
                location.reload(); // ou atualizar só o card editado
            },
        });
    });

    // Pegar tasks 'next' do último dia e colocar no dia a atual como 'todo'
    $("#btnGetPreviousNextTask").click(function (e) {
        e.preventDefault();

        let oldDate = $("#btnGetPreviousNextTask").data("old");
        let todayDate = $("#btnGetPreviousNextTask").data("today");

        $.ajax({
            url: "/get-tasks-from-old-date/" + oldDate + "/" + todayDate,
            method: "get",
            data: {
                _token: csrfToken,
            },
            success: function () {
                location.reload(); // ou atualizar só o card editado
            },
        });
    });

    // Botão para pegar dados do dia anterior e aparecer ao lado das task de hoje
    $('#btnSeePreviousDay').on('click', function() {
        const previousDayColumn = $('#previous-day-kanban-column');

        // Alterna a visibilidade da coluna (adiciona/remove a classe 'hidden')
        previousDayColumn.toggleClass('hidden');

        // Verifica se a coluna já foi carregada para evitar requisições duplicadas
        if (previousDayColumn.data('loaded')) {
            return;
        }

        let oldDate = $("#btnGetPreviousNextTask").data("old");

        // Requisição AJAX para obter as tarefas do dia anterior
        $.ajax({
            url: '/previous-day-tasks', // Substitua pela sua rota real
            method: 'GET',
            data: {
                _token: csrfToken,
                oldDate: oldDate
            },
            success: function(response) {

                // Variável para armazenar o HTML que será gerado
                let previousDayHtml = '';

                // Verifica se a resposta contém os dados das listas e tarefas
                if (response && response.listas && response.tasks) {

                    const listasArray = Object.values(response.listas);

                    // Loop através das listas (ex: 'NEXT', 'PROGRESS', 'DONE')
                    listasArray.forEach(lista => {
                        previousDayHtml += `
                            <section>
                                <h2 class="text-xl font-semibold mb-3">${lista.toUpperCase()}</h2>
                                <ul class="lista" id="lista-anterior-${lista.toLowerCase().replace(' ', '-')}}">
                        `;

                        // Loop através das tarefas de cada lista
                        const tasksForList = response.tasks[lista] || [];
                        tasksForList.forEach(task => {
                            let tagsHtml = '';
                            if (task.tags && task.tags.length > 0) {
                                tagsHtml = `
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        ${task.tags.map(tag => `<span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs">${tag.name}</span>`).join('')}
                                    </div>
                                `;
                            }

                            previousDayHtml += `
                                <li class="card p-3 bg-white rounded shadow mb-2" data-id="${task.id}">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-bold text-gray-800">${task.title}</h3>
                                            ${task.notes ? `<p class="text-gray-600 text-sm mt-1">${task.notes}</p>` : ''}
                                            ${tagsHtml}
                                        </div>
                                        <button class="edit-task text-gray-500 hover:text-blue-600 cursor-pointer" title="Editar">
                                            ✏️
                                        </button>
                                    </div>
                                </li>
                            `;
                        });

                        previousDayHtml += `
                                </ul>
                            </section>
                        `;
                    });
                }

                // Insere o HTML gerado na div de "previous-day-kanban-column"
                console.log(previousDayHtml);
                previousDayColumn.html(previousDayHtml);

                // Marca a coluna como carregada
                previousDayColumn.data('loaded', true);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao buscar as tarefas do dia anterior:', error);
                // Opcional: mostrar uma mensagem de erro para o usuário
                previousDayColumn.html('<p class="text-red-500">Não foi possível carregar as tarefas do dia anterior.</p>');
            }
        });
    });

});
