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
        width: 400,
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
});
