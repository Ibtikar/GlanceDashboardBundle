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

    $('div.panel-flat').on('click', '.dev-publish-recipe', function () {
        $('[data-popup="tooltip"]').tooltip("hide");
        blockPage();
        showPublishModal($(this));
    });
})


function  showPublishModal(element) {
    var basicModal = new BasicModal();
    basicModal.show(publisUrl + '?id=' + element.attr('data-id'), function () {
        unblockPage();
        $(".dev-save-publish-location").click(function () {
            if (recipeStatus != "published") {
                if ($('.open-datetimepicker').val() == '' && $('#publishNow').prop('checked') == false) {
                    $('#dev-publish-modal').find('.alert.alert-danger').remove();
                    $('#dev-publish-modal').prepend('<div class="alert alert-danger no-border"><button data-dismiss="alert" class="close" type="button">' +
                            '<span>×</span><span class="sr-only">Close</span></button>' + publishErrorMessage
                            + '</div>');
                } else {
                    savepublishLocation(basicModal, publisUrl);
                }

            }

        })
    });
}

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
                        $('.dev-new-recipe').html(data.newRecipeCount);
                        $('.dev-new-assign-recipe').html(data.assignedRecipeCount);
                        $('.dev-autopublish-recipe').html(data.autopublishRecipeCount);
                        $('.dev-published-recipe').html(data.publishRecipeCount);
                        $('.dev-deleted-recipe').html(data.deletedRecipeCount);
                    } else {
                        showNotificationMsg(data.message, "", 'error');
                    }
                }, false)

            }

        });
    }

}

function savepublishLocation(basicModal, url) {
    //modified to use this way instead of form serialize to fix this bug #3535:
    if($('.dev-save-publish-location').attr('ajax-running')){
        return;
    }
    $('.dev-save-publish-location').attr('ajax-running', true)
    $('.dev-save-publish-location').append('<i class="icon-spinner6 spinner position-right"></i>');

    $.ajax({
        url: url,
        method: 'POST',
        data: $('#dev-publish-modal').serialize(),
        success: function (data) {
//            console.log('hnaa')
            if (data.status == 'login') {
                window.location.reload(true);

            } else {
                if (data.status == 'error') {
                    $('.dev-save-publish-location').find('.icon-spinner6.spinner.position-right').remove();
                    $('.dev-save-publish-location').removeAttr('ajax-running');
                    $('#dev-publish-modal').find('.alert.alert-danger').remove();
                    $('#dev-publish-modal').prepend('<div class="alert alert-danger no-border"><button data-dismiss="alert" class="close" type="button">' +
                            '<span>×</span><span class="sr-only">Close</span></button>' + data.message
                            + '</div>');
                } else {
                    basicModal.hide();
                    table.ajax.reload(function () {
                        if (data.status != 'reload-table') {
                            showNotificationMsg(data.message, "", data.status);
                            $('.dev-new-recipe').html(data.newRecipeCount);
                            $('.dev-new-assign-recipe').html(data.assignedRecipeCount);
                            $('.dev-autopublish-recipe').html(data.autopublishRecipeCount);
                            $('.dev-published-recipe').html(data.publishRecipeCount);
                            $('.dev-deleted-recipe').html(data.deletedRecipeCount);
                        } else {

                            showNotificationMsg(data.message, "", 'error');
                        }
                    }, false)
                }
            }
        }
    });
}