$(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    function loadAnalytics() {
        const month = $('#month-select').val();
        if (!month) return;

        $('#analytics-results').addClass('hidden');
        $('#analytics-empty').addClass('hidden');
        $('#analytics-loading').removeClass('hidden');

        $.ajax({
            url: '/api/analytics/month',
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
                            `<span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs mr-1">${tag.name}</span>`
                        ).join('');
                    }

                    const row = `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 border-b font-medium text-gray-800">
                                ${task.title}
                            </td>
                            <td class="px-4 py-3 border-b text-sm text-gray-600">${dateFormatted}</td>
                            <td class="px-4 py-3 border-b text-sm">
                                <span class="uppercase font-semibold text-xs px-2 py-1 rounded bg-blue-50 text-blue-600 border border-blue-100">
                                    ${task.status}
                                </span>
                            </td>
                            <td class="px-4 py-3 border-b text-sm">${tagsHtml}</td>
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
