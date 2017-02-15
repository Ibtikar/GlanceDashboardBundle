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

    $('div.panel-flat').on('click', '.dev-delete-bulk-recipe', function () {
        $('[data-popup="tooltip"]').tooltip("hide");
        blockPage();
        showBulkDeleteModal($(this));
    });

    $('div.panel-flat').on('click', '.dev-delete-single-recipe', function () {
        $('[data-popup="tooltip"]').tooltip("hide");
        blockPage();
        showDeleteModal($(this));
    });
})


function showDeleteModal(clickedElement) {


    var basicModal = new BasicModal();
    basicModal.show(clickedElement.attr('data-href'), function () {
        unblockPage();
        $(".dev-save-delete-recipe").click(function () {
            if ($.trim($('#dev-delete-reason').val())) {
                if ($('.dev-save-delete-recipe').attr('ajax-running')) {
                    return;
                }
                $('.dev-save-delete-recipe').attr('ajax-running', true)
                $('.dev-save-delete-recipe').append('<i class="icon-spinner6 spinner position-right"></i>');
                var Params = {id: clickedElement.attr("data-id"), 'reason': $('#dev-delete-reason').val()};
                $.ajax({
                    url: $(this).attr("data-url"),
                    data: Params,
                    method: 'post',
                    success: function (data) {
                        basicModal.hide()
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
            } else {
                $('#dev-delete-modal').find('.alert.alert-danger').remove();
                $('#dev-delete-modal').prepend('<div class="alert alert-danger no-border"><button data-dismiss="alert" class="close" type="button">' +
                        '<span>×</span><span class="sr-only">Close</span></button>' + deleteErrorMessage
                        + '</div>');

            }
        });
    });
}

 var type='';

function showBulkDeleteModal(clickedElementIfNotBulk) {


    var basicModal = new BasicModal();

    $('tbody .dev-checkbox:checked').each(function(index,value){
        if(index==0){
           type=$(value).attr('data-type');
        }else{
            if(type!= $(value).attr('data-type') ){
                type='all';
            }
        }
    })
    basicModal.show(deleteUrl + '?count=' + $('tbody .dev-checkbox:checked').length+'&type='+type, function () {
        unblockPage();
        $(".dev-save-delete-recipe").click(function () {
            if ($.trim($('#dev-delete-reason').val())) {
                if ($('.dev-save-delete-recipe').attr('ajax-running')) {
                    return;
                }
                $('.dev-save-delete-recipe').attr('ajax-running', true)
                $('.dev-save-delete-recipe').append('<i class="icon-spinner6 spinner position-right"></i>');
                $('#dev-bulk-action').val('Delete');
                recipeBulkFunction();

            } else {
                $('#dev-delete-modal').find('.alert.alert-danger').remove();
                $('#dev-delete-modal').prepend('<div class="alert alert-danger no-border"><button data-dismiss="alert" class="close" type="button">' +
                        '<span>×</span><span class="sr-only">Close</span></button>' + deleteErrorMessage
                        + '</div>');

            }
        });
    });
}

function recipeBulkFunction() {
    $('tr[data-id]').each(function () {
        $(this).removeClass('success').removeClass('danger').find('td:last').html('');
    });

    $('#DocumentType').val(type);
    $('#reason').val($('#dev-delete-reason').val());

    var $form = $('.dev-bulk-actions-form');
    var formData = $form.serializeArray();

    var numOfRecords = $('tr[data-id]').length;
    var numOfCheckedRecord = $('tbody .dev-checkbox:checked').length;
    var pageNum = getQueryVariable('page');
    blockPage();
    $.ajax({
        url: $form.attr('action'),
        data: formData,
        type: $form.attr('method'),
        complete: function () {

        },
        success: function (data) {

           var status = "success";

            if(data.status != "success"){
                status = "error";
            }
            showNotificationMsg(data.message,"",status);

            unblockPage();

            if (data.status == 'success') {
                if (((data.success).length == numOfCheckedRecord || (data.success).length == 0) && pageNum != 1 && numOfRecords === numOfCheckedRecord) {
                    table.page(parseInt(table.page(), 10) - parseInt(1, 10));
                }
                table.ajax.reload(function (){
                    $('.dev-new-recipe').html(data.newRecipeCount);
                    $('.dev-new-assign-recipe').html(data.assignedRecipeCount);
                    $('.dev-autopublish-recipe').html(data.autopublishRecipeCount);
                    $('.dev-published-recipe').html(data.publishRecipeCount);
                    $('.dev-deleted-recipe').html(data.deletedRecipeCount);
                    for (message in data.errors) {
                        for (index in data.errors[message]) {
                            var $tr = $('input[value="' + data.errors[message][index] + '"]').parents('tr');
                            if ($tr.length !== 0) {
                                $(".dev-list-table").removeClass("dev-hide-errors");
                                $tr.find('input[type="checkbox"]').prop('checked',true).uniform('refresh');
                                $tr.addClass('danger').attr('title',message);
                                $tr.powerTip({followMouse: true});

                            }
                        }
                    }
                    showBulkActionSelect();

                }, false);
            }else{
               table.ajax.reload(function (){
                showBulkActionSelect();
               })
            }

        $('#modal_theme_primary').modal('hide')
        }
    });
}

function  showPublishModal(element) {
    var basicModal = new BasicModal();
    basicModal.show(publisUrl + '?id=' + element.attr('data-id'), function () {
        unblockPage();
        $(".dev-save-publish-location").click(function () {
            if (recipeStatus != "publish") {
                if ($('#datetimepicker1 input').val() == '' && $('#publishNow').prop('checked') == false) {
                    $('#dev-publish-modal').find('.alert.alert-danger').remove();
                    $('#dev-publish-modal').prepend('<div class="alert alert-danger no-border"><button data-dismiss="alert" class="close" type="button">' +
                            '<span>×</span><span class="sr-only">Close</span></button>' + publishErrorMessage
                            + '</div>');
                } else {
                    savepublishLocation(basicModal, publisUrl);
                }

            } else {
                savepublishLocation(basicModal, publisUrl);
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
    if ($('.dev-save-publish-location').attr('ajax-running')) {
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
                    $('#dev-publish-modal').prepend('<div class="alert alert-danger no-border"><button data-dismiss="alert" class="close" type="button">' + '<span>×</span><span class="sr-only">Close</span></button>' + data.message + '</div>');
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