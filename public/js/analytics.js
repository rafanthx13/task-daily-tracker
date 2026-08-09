$(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function loadAnalytics() {
        const month = $('#month-select').val();
        if (!month) return;

        $('#analytics-results').addClass('hidden');
        $('#analytics-empty').addClass('hidden');
        $('#analytics-loading').removeClass('hidden');

        $.ajax({
            url: window.APP_URL + '/api/analytics/month',
            method: 'GET',
            data: { month: month },
            success: function (response) {
                $('#analytics-loading').addClass('hidden');

                if (!response.tasks || response.tasks.length === 0) {
                    $('#analytics-empty').removeClass('hidden');
                    return;
                }

                $('#analytics-results').removeClass('hidden');

                // Update stats
                $('#stat-total').text(response.summary.total);

                // Update table
                const tbody = $('#tasks-table-body');
                tbody.empty();

                response.tasks.forEach(task => {
                    const dateObj = new Date(task.date);
                    const dateFormatted = dateObj.toLocaleDateString('pt-BR');

                    let tagsHtml = '';
                    if (task.tags && task.tags.length > 0) {
                        tagsHtml = task.tags.map(tag =>
                            `<span class="px-2 py-0.5 rounded-full text-xs font-medium border mr-1" style="background-color: ${tag.color || '#E0E7FF'}20; color: ${tag.color || '#3730A3'}; border-color: ${tag.color || '#E0E7FF'}40;">${tag.name}</span>`
                        ).join('');
                    }

                    let statusClasses = 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-100 dark:border-gray-700'; // Default
                    if (task.status === 'todo') {
                        statusClasses = 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900';
                    } else if (task.status === 'wating') { // Note: misspelled in constants
                        statusClasses = 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900';
                    } else if (task.status === 'done') {
                        statusClasses = 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900';
                    }

                    const row = `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-medium text-gray-800 dark:text-gray-200">
                                <a href="${window.APP_URL}/tasks/view/${task.id}" class="hover:text-blue-600 dark:hover:text-blue-400 hover:underline transition-colors">
                                    ${task.title}
                                </a>
                            </td>
                            <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">${dateFormatted}</td>
                            <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-sm">
                                <span class="uppercase font-semibold text-[10px] px-2 py-0.5 rounded border ${statusClasses}">
                                    ${task.status}
                                </span>
                            </td>
                            <td class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-sm">${tagsHtml}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            },
            error: function (xhr) {
                $('#analytics-loading').addClass('hidden');
                console.error('Erro ao carregar analytics:', xhr.responseText);
                if (typeof showNotification === 'function') {
                    showNotification('Erro ao carregar os dados do relatório.', 'error');
                } else {
                    alert('Erro ao carregar os dados do relatório.');
                }
            }
        });
    }

    $('#btn-load-analytics').on('click', function () {
        loadAnalytics();
    });

    // Load initial data
    loadAnalytics();
});
