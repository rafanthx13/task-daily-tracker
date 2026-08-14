$(function () {

    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function refreshCsrfToken() {
        return $.ajax({
            url: `${window.APP_URL}/csrf-token`,
            method: 'GET',
            cache: false
        }).then(function(response) {
            csrfToken = response.token;
            document.querySelector('meta[name="csrf-token"]').setAttribute('content', csrfToken);

            return csrfToken;
        });
    }

    function showNotification(message, type = 'success') {
        const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const notification = $(`
            <div class="pointer-events-auto ${bgColor} text-white px-6 py-3 rounded-lg shadow-xl mb-3 flex justify-between items-center transition-all duration-500 transform translate-x-full opacity-0">
                <span>${message}</span>
                <button class="ml-4 font-bold text-white hover:text-gray-200 cursor-pointer">&times;</button>
            </div>
        `);

        $('#notification-container').append(notification);

        if (prefersReducedMotion()) {
            notification.removeClass('translate-x-full opacity-0');
        } else {
            setTimeout(() => {
                notification.removeClass('translate-x-full opacity-0');
            }, 10);
        }

        function dismissNotification() {
            if (prefersReducedMotion()) {
                notification.remove();
                return;
            }

            notification.addClass('translate-x-full opacity-0');
            setTimeout(() => notification.remove(), 500);
        }

        // Remove on click
        notification.find('button').on('click', function() {
            dismissNotification();
        });

        // Auto remove
        setTimeout(() => {
            if (notification.parent().length) {
                dismissNotification();
            }
        }, 5000);
    }

    window.showNotification = showNotification;

    function getRequestErrorMessage(xhr, fallback = 'Não foi possível concluir a operação. Tente novamente.') {
        const response = xhr && xhr.responseJSON ? xhr.responseJSON : {};

        if (!xhr || xhr.status === 0) {
            return 'Não foi possível conectar ao servidor. Verifique sua conexão e tente novamente.';
        }

        if (xhr.status === 419) {
            return 'Sua sessão expirou. Recarregue a página e tente novamente.';
        }

        if (xhr.status === 404) {
            return 'O registro não foi encontrado. Ele pode ter sido removido em outra aba.';
        }

        if (xhr.status === 409) {
            return 'Este registro foi alterado em outra aba. Recarregue a página e tente novamente.';
        }

        if (xhr.status === 422) {
            const firstError = Object.values(response.errors || {}).flat().find(Boolean);
            return firstError || response.message || 'Verifique os dados informados e tente novamente.';
        }

        if (xhr.status >= 500) {
            return 'Ocorreu um erro interno. Tente novamente em alguns instantes.';
        }

        return response.message || fallback;
    }

    window.getRequestErrorMessage = getRequestErrorMessage;

    function setButtonLoading(button, isLoading, loadingLabel, defaultLabel) {
        button
            .prop('disabled', isLoading)
            .attr('aria-busy', String(isLoading))
            .text(isLoading ? loadingLabel : defaultLabel);
    }

    function updateThemeToggle(isDark) {
        $('#themeToggle')
            .attr('aria-pressed', String(isDark))
            .attr('title', isDark ? 'Ativar modo claro' : 'Ativar modo escuro');
    }

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Remove any property handler left by a hot reload before binding jQuery.
        themeToggle.onclick = null;
    }

    updateThemeToggle(document.documentElement.classList.contains('dark'));

    // This is the only listener responsible for switching the theme.
    $('#themeToggle').off('click.darkMode').on('click.darkMode', function () {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeToggle(isDark);
    });

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
                        showNotification(getRequestErrorMessage(xhr, 'Não foi possível atualizar a raia da tarefa.'), 'error');

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
            url: `${window.APP_URL}/tasks/${id}`,
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
            error: function (xhr) {
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível atualizar a tarefa.'), 'error');
            },
        });
    });

    // Deletar Task
    $("#deleteTaskForm").click(function (e) {
        e.preventDefault();


        let id = $("#editTaskId").val();

        $.ajax({
            url: `${window.APP_URL}/tasks/${id}`,
            method: "DELETE",
            data: {
                _token: csrfToken,
            },
            success: function () {
                location.reload(); // ou atualizar só o card editado
            },
            error: function (xhr) {
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível excluir a tarefa.'), 'error');
            },
        });
    });

    // Pegar tasks 'next' do último dia e colocar no dia a atual como 'todo'
    $("#btnGetPreviousNextTask").click(function (e) {
        e.preventDefault();

        const button = $(this);
        const oldDate = button.data("old");
        const todayDate = button.data("today");

        setButtonLoading(button, true, 'Copiando tarefas...', 'Carregar dia anterior');

        $.ajax({
            url: `${window.APP_URL}/get-tasks-from-old-date/${oldDate}/${todayDate}`,
            method: "get",
            data: {
                _token: csrfToken,
            },
            success: function (response) {
                // console.log(response);
                location.reload();
            },
            error: function (xhr) {
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível copiar as tarefas.'), 'error');
            },
            complete: function () {
                setButtonLoading(button, false, 'Copiando tarefas...', 'Carregar dia anterior');
            }
        });

    });

    // Botão para pegar dados do dia anterior e aparecer ao lado das task de hoje
    $('#btnSeePreviousDay').on('click', function() {
        const button = $(this);
        const previousDayColumn = $('#previous-day-kanban-column');

        // Alterna a visibilidade da coluna (adiciona/remove a classe 'hidden')
        previousDayColumn.toggleClass('hidden');

        // Mantém o estado visual do botão sincronizado com a coluna.
        const isPreviousDayVisible = !previousDayColumn.hasClass('hidden');
        button
            .attr('data-active', String(isPreviousDayVisible))
            .attr('aria-expanded', String(isPreviousDayVisible))
            .text(isPreviousDayVisible ? 'Ocultar dia anterior' : 'Ver dia anterior');

        // Verifica se a coluna já foi carregada para evitar requisições duplicadas
        if (previousDayColumn.data('loaded')) {
            return;
        }

        let oldDate = $("#btnGetPreviousNextTask").data("old");
        const loading = $('#previous-day-loading');

        button.prop('disabled', true).attr('aria-busy', 'true');
        previousDayColumn.attr('aria-busy', 'true').empty().append(loading.removeClass('hidden'));

        // Requisição AJAX para obter as tarefas do dia anterior
        $.ajax({
            url: `${window.APP_URL}/previous-day-tasks`, // Substitua pela sua rota real
            method: 'GET',
            data: {
                _token: csrfToken,
                oldDate: oldDate
            },
            success: function(response) {
                const data = response.data;

                previousDayColumn.empty();

                // Verifica se a resposta contém os dados das listas e tarefas
                if (data && data.listas && data.tasks) {
                    const formattedDate = new Intl.DateTimeFormat('pt-BR', {
                        dateStyle: 'full',
                    }).format(new Date(`${data.date}T00:00:00`));
                    const $header = $('<div>', {
                        class: 'mb-5 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-900 dark:bg-blue-950/40',
                    });

                    $('<h2>', {
                        class: 'text-base font-bold text-blue-900 dark:text-blue-100',
                    }).text(`Tarefas de ${formattedDate}`).appendTo($header);

                    $('<span>', {
                        class: 'rounded-full border border-blue-300 bg-white px-2 py-0.5 text-xs font-medium text-blue-700 dark:border-blue-700 dark:bg-gray-900 dark:text-blue-300',
                    }).text('Somente leitura').appendTo($header);

                    previousDayColumn.append($header);

                    const listasArray = Object.values(data.listas);

                    listasArray.forEach(lista => {
                        const $section = $('<section>', {
                            class: 'mb-6',
                        });
                        $('<h3>', {
                            class: 'mb-3 text-xl font-semibold',
                        }).text(String(lista).toUpperCase()).appendTo($section);

                        const $list = $('<ul>', {
                            class: 'lista',
                            id: `lista-anterior-${String(lista).toLowerCase().replace(' ', '-')}`,
                        }).appendTo($section);

                        // Loop através das tarefas de cada lista
                        const tasksForList = data.tasks[lista] || [];
                        tasksForList.forEach(task => {
                            const tags = Array.isArray(task.tags) ? task.tags : [];
                            const $card = $('<li>', {
                                class: 'card mb-2 rounded bg-white p-3 shadow dark:bg-gray-800',
                                'aria-label': 'Tarefa somente leitura',
                            });
                            const $taskContent = $('<div>').appendTo($card);
                            const $titleRow = $('<div>', {
                                class: 'flex items-center gap-2 flex-wrap',
                            }).appendTo($taskContent);

                            $('<h3>', {
                                class: 'font-bold text-gray-800',
                            }).text(task.title || '').appendTo($titleRow);

                            if (tags.length > 0) {
                                const $tags = $('<div>', {
                                    class: 'flex flex-wrap gap-1',
                                }).appendTo($titleRow);

                                tags.forEach(tag => {
                                    const color = tag.color || '#3730A3';
                                    $('<span>', {
                                        class: 'px-2 py-0.5 rounded-full text-xs font-medium border',
                                    }).css({
                                        backgroundColor: `${color}20`,
                                        color: color,
                                        borderColor: `${color}40`,
                                    }).text(tag.name || '').appendTo($tags);
                                });
                            }

                            if (task.notes) {
                                $('<p>', {
                                    class: 'text-gray-600 text-sm mt-1',
                                }).text(task.notes).appendTo($taskContent);
                            }

                            $list.append($card);
                        });

                        previousDayColumn.append($section);
                    });
                }

                // Marca a coluna como carregada
                previousDayColumn.data('loaded', true);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao buscar as tarefas do dia anterior:', error);
                previousDayColumn.html('<p class="text-red-500">Não foi possível carregar as tarefas do dia anterior.</p>');
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível carregar as tarefas do dia anterior.'), 'error');
            },
            complete: function() {
                button.prop('disabled', false).attr('aria-busy', 'false');
                previousDayColumn.attr('aria-busy', 'false').append(loading.addClass('hidden'));
            }
        });
    });
    // Toggle da seção de lembretes
    $('#btnToggleReminders').on('click', function() {
        const section = $('#reminders-section');
        section.toggleClass('hidden');

        if (section.hasClass('hidden')) {
            $(this).text('Mostrar Lembretes').removeClass('bg-pink-800').addClass('bg-pink-600');
        } else {
            $(this).text('Ocultar Lembretes').removeClass('bg-pink-600').addClass('bg-pink-800');
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
            success: function(response) {
                // Aplica o estilo de concluído em vez de remover
                btn.removeClass('bg-white text-pink-700 border-pink-200 hover:bg-pink-700 hover:text-white hover:border-pink-700 opacity-50')
                   .addClass('bg-gray-200 text-gray-500 border-gray-300 line-through cursor-default')
                   .prop('disabled', true)
                   .attr('title', 'Concluído hoje');

                showNotification(response.message);
            },
            error: function(xhr) {
                btn.prop('disabled', false).removeClass('opacity-50');
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível concluir o lembrete.'), 'error');
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
            success: function(response) {
                if (prefersReducedMotion()) {
                    item.remove();
                } else {
                    item.fadeOut(300, function() {
                        $(this).remove();
                    });
                }
                showNotification(response.message);
            },
            error: function(xhr) {
                btn.prop('disabled', false).removeClass('opacity-50');
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível finalizar o lembrete.'), 'error');
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
            resizeDaySummaryTextarea();
        }
    });

    function resizeDaySummaryTextarea() {
        const textarea = document.getElementById('daySummaryText');
        if (!textarea) return;

        textarea.style.height = 'auto';
        textarea.style.height = `${textarea.scrollHeight}px`;
    }

    $('#daySummaryText').on('input', resizeDaySummaryTextarea);
    resizeDaySummaryTextarea();

    // Alterna entre a edição do Markdown e sua visualização renderizada.
    $('#btnPreviewSummary').on('click', function() {
        const textarea = $('#daySummaryText');
        const preview = $('#daySummaryPreview');
        const btn = $(this);

        if (!preview.hasClass('hidden')) {
            preview.addClass('hidden').empty();
            textarea.removeClass('hidden');
            btn.prop('disabled', false)
                .removeClass('opacity-50')
                .attr('aria-pressed', 'false')
                .text('Visualizar Markdown');
            resizeDaySummaryTextarea();
            textarea.trigger('focus');
            return;
        }

        btn.prop('disabled', true).addClass('opacity-50').text('Renderizando...');

        refreshCsrfToken()
            .then(function(freshToken) {
                return $.ajax({
                    url: `${window.APP_URL}/day-summary/preview`,
                    method: 'POST',
                    data: {
                        _token: freshToken,
                        content: textarea.val()
                    }
                });
            })
            .done(function(response) {
                preview.html(response.data.html || '<p class="text-gray-500 dark:text-gray-400">Nenhum conteúdo para visualizar.</p>');
                textarea.addClass('hidden');
                preview.removeClass('hidden');
                btn.attr('aria-pressed', 'true').text('Editar Markdown');
            })
            .fail(function(xhr) {
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível renderizar o resumo.'), 'error');
                btn.attr('aria-pressed', 'false').text('Visualizar Markdown');
            })
            .always(function() {
                btn.prop('disabled', false).removeClass('opacity-50');
            });
    });

    // Salvar resumo do dia
    $('#btnSaveSummary').on('click', function() {
        const textarea = $('#daySummaryText');
        const content = textarea.val();
        const date = textarea.data('date');
        const btn = $(this);

        setButtonLoading(btn, true, 'Salvando...', 'Salvar Resumo');

        // Obtém um token novo antes de salvar. Isso também renova a sessão caso
        // a página tenha permanecido aberta além do SESSION_LIFETIME.
        refreshCsrfToken()
            .then(function(freshToken) {
                return $.ajax({
                    url: `${window.APP_URL}/day-summary`,
                    method: 'POST',
                    data: {
                        _token: freshToken,
                        date: date,
                        content: content
                    }
                });
            })
            .done(function(response) {
                showNotification(response.message);
            })
            .fail(function(xhr) {
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível salvar o resumo.'), 'error');
            })
            .always(function() {
                setButtonLoading(btn, false, 'Salvando...', 'Salvar Resumo');
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
                const data = response.data;
                timeManagementTags = data.tags;
                $('#time-entries-body').empty();
                if (data.entries.length > 0) {
                    data.entries.forEach(entry => addTimeRow(entry));
                } else {
                    addTimeRow(); // start with one empty row
                }
                $('#time-management-section').data('loaded', true);
            },
            error: function(xhr) {
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível carregar os registros de tempo.'), 'error');
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
        setButtonLoading(btn, true, 'Salvando...', 'Salvar Tempo');

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
            },
            error: function(xhr) {
                showNotification(getRequestErrorMessage(xhr, 'Não foi possível salvar os registros de tempo.'), 'error');
            },
            complete: function() {
                setButtonLoading(btn, false, 'Salvando...', 'Salvar Tempo');
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
