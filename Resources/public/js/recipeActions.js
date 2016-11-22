var assign = true;

$(document).ready(function () {
    $('a[data-toggle="tab"]').on('click', function (e) {
        blockPage();
        window.location.href = $(this).attr('data-href');

    });

    $(document).on('click', '.dev-assign-to-me', function (e) {
        e.preventDefault();
        blockPage();
        assignToMe($(this));
    });
})

function  assignToMe(clickedElement) {
    var Params = {recipeId: clickedElement.attr("data-id")};
    if (assign) {
        assign = false;
        $.ajax({
            url: clickedElement.attr("data-url"),
            data: Params,
            method: 'post',
            success: function (data) {
                assign = true;
                table.ajax.reload(function () {
                    if (data.status != 'reload-table') {
                        showNotificationMsg(data.message, "", data.status);
                        $('.dev-new-recipe').html(data.newRecipe);
                        $('.dev-new-assign-recipe').html(data.assignedRecipe);
                    } else {
                        showNotificationMsg(data.message, "", 'error');
                    }
                }, false)

            }

        });
    }

}