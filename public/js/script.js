$(function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showNotification(message, type = 'success') {
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const notification = $(`
            <div class="pointer-events-auto ${bgColor} text-white px-6 py-3 rounded-lg shadow-xl mb-3 flex justify-between items-center transition-all duration-500 transform translate-x-full opacity-0">
                <span>${message}</span>
                <button class="ml-4 font-bold text-white hover:text-gray-200 cursor-pointer">&times;</button>
            </div>
        `);

        $('#notification-container').append(notification);

        // Animate in
        setTimeout(() => {
            notification.removeClass('translate-x-full opacity-0');
        }, 10);

        // Remove on click
        notification.find('button').on('click', function() {
            notification.addClass('translate-x-full opacity-0');
            setTimeout(() => notification.remove(), 500);
        });

        // Auto remove
        setTimeout(() => {
            if (notification.parent().length) {
                notification.addClass('translate-x-full opacity-0');
                setTimeout(() => notification.remove(), 500);
            }
        }, 5000);
    }


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
        let tags = card.data("tags"); // Array de IDs

        $("#editTaskId").val(id);
        $("#editTaskTitle").val(title);
        $("#editTaskNotes").val(notes);
        $("#editTaskTags").val(tags);

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
        let tag_ids = $("#editTaskTags").val();

        $.ajax({
            url: "/tasks/" + id,
            method: "PUT",
            data: {
                title: title,
                notes: notes,
                tag_ids: tag_ids,
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
            success: function (response) {
                // console.log(response);
                location.reload();
            },
            error: function (xhr) {
                let errorMessage = "Erro ao copiar tarefas.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showNotification(errorMessage, 'error');
            }
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
                                <ul class="lista" id="lista-anterior-${lista.toLowerCase().replace(' ', '-')}">
                        `;

                        // Loop através das tarefas de cada lista
                        const tasksForList = response.tasks[lista] || [];
                        tasksForList.forEach(task => {
                            let tagsHtml = '';
                            let tagIds = [];
                            if (task.tags && task.tags.length > 0) {
                                tagIds = task.tags.map(t => t.id);
                                tagsHtml = `
                                    <div class="flex flex-wrap gap-1">
                                        ${task.tags.map(tag => `<span class="px-2 py-0.5 rounded-full text-xs font-medium border" style="background-color: ${tag.color || '#E0E7FF'}20; color: ${tag.color || '#3730A3'}; border-color: ${tag.color || '#E0E7FF'}40;">${tag.name}</span>`).join('')}
                                    </div>
                                `;
                            }

                            previousDayHtml += `
                                <li class="card p-3 bg-white rounded shadow mb-2" data-id="${task.id}" data-tags='${JSON.stringify(tagIds)}'>
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h3 class="font-bold text-gray-800">${task.title}</h3>
                                                ${tagsHtml}
                                            </div>
                                            ${task.notes ? `<p class="text-gray-600 text-sm mt-1">${task.notes}</p>` : ''}
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
