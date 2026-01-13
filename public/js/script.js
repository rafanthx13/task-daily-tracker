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

    window.showNotification = showNotification;


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
                    url: `${window.APP_URL}/tasks/change-lane/${taskId}`,
                    method: "PUT",
                    data: {
                        _token: csrfToken,
                        status: newStatus.toLowerCase(),
                    },
                    success: function (response) {
                        console.log("Status atualizado:", response);
                        // showNotification("Status atualizado com sucesso!"); // Optional
                    },
                    error: function (xhr) {
                        console.error(
                            "Erro ao atualizar status:",
                            xhr.responseText
                        );
                        let errorMessage = "Erro ao atualizar o status da tarefa.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        showNotification(errorMessage, 'error');

                        // Opcional: Reverter o movimento do item se der erro?
                        // Por simplicidade apenas avisamos o erro por enquanto.
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
            url: window.APP_URL + "/tasks/" + id,
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
            url: window.APP_URL + "/tasks/" + id,
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
            url: window.APP_URL + "/get-tasks-from-old-date/" + oldDate + "/" + todayDate,
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
            url: window.APP_URL + '/previous-day-tasks', // Substitua pela sua rota real
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
    // Toggle da seção de lembretes
    $('#btnToggleReminders').on('click', function() {
        const section = $('#reminders-section');
        section.toggleClass('hidden');

        if (section.hasClass('hidden')) {
            $(this).text('Mostrar Lembretes').removeClass('bg-pink-700').addClass('bg-pink-500');
        } else {
            $(this).text('Ocultar Lembretes').removeClass('bg-pink-500').addClass('bg-pink-700');
        }
    });

    // Concluir lembrete recorrente
    $(document).on('click', '.complete-recurring-tag', function() {
        const id = $(this).data('id');
        const btn = $(this);

        if (btn.prop('disabled')) return;

        btn.prop('disabled', true).addClass('opacity-50');

        $.ajax({
            url: `${window.APP_URL}/reminders/${id}/complete`,
            method: 'POST',
            data: {
                _token: csrfToken
            },
            success: function() {
                // Aplica o estilo de concluído em vez de remover
                btn.removeClass('bg-white text-blue-600 border-blue-100 hover:bg-blue-600 hover:text-white hover:border-blue-600 opacity-50')
                   .addClass('bg-gray-200 text-gray-500 border-gray-300 line-through cursor-default')
                   .prop('disabled', true)
                   .attr('title', 'Concluído hoje');

                showNotification("Lembrete concluído!");
            },
            error: function() {
                btn.prop('disabled', false).removeClass('opacity-50');
                showNotification("Erro ao concluir lembrete.", "error");
            }
        });
    });

    // Finalizar lembrete esporádico
    $(document).on('click', '.finish-sporadic-btn', function() {
        const id = $(this).data('id');
        const btn = $(this);
        const item = btn.closest('li');

        btn.prop('disabled', true).addClass('opacity-50');

        $.ajax({
            url: `${window.APP_URL}/reminders/${id}/finish-sporadic`,
            method: 'POST',
            data: {
                _token: csrfToken
            },
            success: function() {
                item.fadeOut(300, function() {
                    $(this).remove();
                });
                showNotification("Lembrete finalizado!");
            },
            error: function() {
                btn.prop('disabled', false).removeClass('opacity-50');
                showNotification("Erro ao finalizar lembrete.", "error");
            }
        });
    });

    // Toggle da seção de resumo do dia
    $('#btnToggleSummary').on('click', function() {
        const section = $('#day-summary-section');
        section.toggleClass('hidden');

        if (section.hasClass('hidden')) {
            $(this).removeClass('bg-emerald-700').addClass('bg-emerald-500');
        } else {
            $(this).removeClass('bg-emerald-500').addClass('bg-emerald-700');
        }
    });

    // Salvar resumo do dia
    $('#btnSaveSummary').on('click', function() {
        const textarea = $('#daySummaryText');
        const content = textarea.val();
        const date = textarea.data('date');
        const btn = $(this);

        btn.prop('disabled', true).addClass('opacity-50').text('Salvando...');

        $.ajax({
            url: `${window.APP_URL}/day-summary`,
            method: 'POST',
            data: {
                _token: csrfToken,
                date: date,
                content: content
            },
            success: function(response) {
                btn.prop('disabled', false).removeClass('opacity-50').text('Salvar Resumo');
                showNotification(response.message);
            },
            error: function() {
                btn.prop('disabled', false).removeClass('opacity-50').text('Salvar Resumo');
                showNotification("Erro ao salvar resumo.", "error");
            }
        });
    });

    // --- TIME MANAGEMENT LOGIC ---
    let timeManagementTags = [];
    const currentDate = $('#daySummaryText').data('date');

    function calculateTimeDiff(start, end) {
        if (!start || !end) return '';
        const [h1, m1] = start.split(':').map(Number);
        const [h2, m2] = end.split(':').map(Number);
        let diffMinutes = (h2 * 60 + m2) - (h1 * 60 + m1);
        if (diffMinutes < 0) diffMinutes += 1440; // overnight support
        const h = Math.floor(diffMinutes / 60);
        const m = diffMinutes % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
    }

    function addTimeRow(data = {}) {
        const rowId = Date.now() + Math.random().toString(36).substr(2, 5);
        const options = timeManagementTags.map(tag => `<option value="${tag.id}" ${tag.id == data.tag_id ? 'selected' : ''} style="color: ${tag.color}">${tag.name}</option>`).join('');

        const timeDiff = calculateTimeDiff(data.start_time, data.end_time);

        const row = $(`
            <tr id="row-${rowId}" class="group">
                <td class="p-1 border border-purple-200">
                    <input type="text" class="task-name w-full bg-transparent outline-none" value="${data.task_name || ''}" placeholder="Task name">
                </td>
                <td class="p-1 border border-purple-200">
                    <input type="time" class="start-time w-full bg-transparent outline-none" value="${data.start_time || ''}">
                </td>
                <td class="p-1 border border-purple-200">
                    <input type="time" class="end-time w-full bg-transparent outline-none" value="${data.end_time || ''}">
                </td>
                <td class="p-1 border border-purple-200 text-center font-mono text-purple-600 time-diff">
                    ${timeDiff}
                </td>
                <td class="p-1 border border-purple-200">
                    <select class="tag-id w-full bg-transparent outline-none">
                        <option value="">None</option>
                        ${options}
                    </select>
                </td>
                <td class="p-1 border border-purple-200 text-center">
                    <button class="btnRemoveRow text-red-300 hover:text-red-500 transition cursor-pointer">&times;</button>
                </td>
            </tr>
        `);
        $('#time-entries-body').append(row);
    }

    $('#btnToggleTimeManagement').on('click', function() {
        const section = $('#time-management-section');
        section.toggleClass('hidden');

        if (!section.hasClass('hidden') && !section.data('loaded')) {
            loadTimeEntries();
        }

        if (section.hasClass('hidden')) {
            $(this).removeClass('bg-purple-700').addClass('bg-purple-500');
        } else {
            $(this).removeClass('bg-purple-500').addClass('bg-purple-700');
        }
    });

    function loadTimeEntries() {
        $.ajax({
            url: `${window.APP_URL}/time-management/entries/${currentDate}`,
            method: 'GET',
            success: function(response) {
                timeManagementTags = response.tags;
                $('#time-entries-body').empty();
                if (response.entries.length > 0) {
                    response.entries.forEach(entry => addTimeRow(entry));
                } else {
                    addTimeRow(); // start with one empty row
                }
                $('#time-management-section').data('loaded', true);
            }
        });
    }

    $('#btnAddTimeRow').on('click', function() {
        addTimeRow();
    });

    $(document).on('click', '.btnRemoveRow', function() {
        $(this).closest('tr').remove();
    });

    $(document).on('input', '.start-time, .end-time', function() {
        const row = $(this).closest('tr');
        const start = row.find('.start-time').val();
        const end = row.find('.end-time').val();
        row.find('.time-diff').text(calculateTimeDiff(start, end));
    });

    $('#btnSaveTimeEntries').on('click', function() {
        const entries = [];
        $('#time-entries-body tr').each(function() {
            const task_name = $(this).find('.task-name').val();
            if (task_name) {
                entries.push({
                    task_name: task_name,
                    start_time: $(this).find('.start-time').val(),
                    end_time: $(this).find('.end-time').val(),
                    tag_id: $(this).find('.tag-id').val()
                });
            }
        });

        const btn = $(this);
        btn.prop('disabled', true).text('Salvando...');

        $.ajax({
            url: `${window.APP_URL}/time-management/sync`,
            method: 'POST',
            data: {
                _token: csrfToken,
                date: currentDate,
                entries: entries
            },
            success: function(response) {
                showNotification(response.message);
                btn.prop('disabled', false).text('Salvar Tempo');
            },
            error: function() {
                showNotification("Erro ao salvar registros de tempo.", "error");
                btn.prop('disabled', false).text('Salvar Tempo');
            }
        });
    });

    $('#btnViewAsExcel').on('click', function() {
        const container = $('#excel-view-container');
        container.toggleClass('hidden');

        if (!container.hasClass('hidden')) {
            $(this).text('Esconder Tabela');
            let html = '<table border="1" style="border-collapse: collapse; width: 100%;">';
            html += '<thead><tr><th>Task</th><th>Start</th><th>End</th><th>Time</th><th>Tag</th></tr></thead><tbody>';

            $('#time-entries-body tr').each(function() {
                const task = $(this).find('.task-name').val();
                const start = $(this).find('.start-time').val();
                const end = $(this).find('.end-time').val();
                const time = $(this).find('.time-diff').text().trim();
                const tag = $(this).find('.tag-id option:selected').text();

                if (task) {
                    html += `<tr><td>${task}</td><td>${start}</td><td>${end}</td><td>${time}</td><td>${tag === 'None' ? '' : tag}</td></tr>`;
                }
            });

            html += '</tbody></table>';
            $('#excel-html-table').html(html);
        } else {
            $(this).text('Ver como Tabela (Excel)');
        }
    });

});
