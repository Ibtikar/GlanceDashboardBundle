$(document).ready(function () {
    $('a[data-toggle="tab"]').on('click', function (e) {
        blockPage();
        window.location.href = $(this).attr('data-href');

    });

    $('div.panel-flat').on('click', '.dev-publish-recipe', function () {
        $('[data-popup="tooltip"]').tooltip("hide");
        blockPage();
        showPublishModal($(this));
    });
    $('.dataTables_wrapper').on('click', '.dev-unpublish-btn', function (e) {

        var numOfRecords = $('tbody .dev-checkbox').length;
        var pageNum = getQueryVariable('page');
        $.ajax
                ({
                    'dataType': 'json',
                    'url': $(this).parents('[role="tooltip"]').prev().data('href'),
                    beforeSend: function () {
                        blockPage();
                    },
                    'success': function (json) {
                        var status = "success";

                        if (json.status != "success") {
                            status = "error";
                        }
                        if (json.status == 'success') {
                            if (pageNum != 1 && numOfRecords == 1) {
                                table.page(parseInt(table.page(), 10) - parseInt(1, 10));
                            }
                        }
                        if (typeof listName != 'undefined' && listName == 'competitiion') {
                            $('.dev-new-comptetion').html(json.count.newCount);
                            $('.dev-publish-comptetion').html(json.count.publishCount);
                            $('.dev-unpublish-comptetion').html(json.count.unpublishCount);

                        } else {
                            $('.dev-document-count').html(json.count)
                        }
                        showNotificationMsg(json.message, "", status);
                        unblockPage();
                        $('.dev-bulk-action-container').hide();
                        table.ajax.reload(function () {
                            showBulkActionSelect();
                        }, false);
                    }
                });
    });

})



var type = '';





function  showPublishModal(element) {
    var basicModal = new BasicModal();
    basicModal.show(publisUrl + '?id=' + element.attr('data-id'), function () {
        unblockPage();
        $(".dev-save-publish-location").click(function () {
            savepublishLocation(basicModal, publisUrl);

        })
    });
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
                basicModal.hide();
                if (data.status == 'error') {
                    $('.dev-save-publish-location').find('.icon-spinner6.spinner.position-right').remove();
                    $('.dev-save-publish-location').removeAttr('ajax-running');
                    $('#dev-publish-modal').find('.alert.alert-danger').remove();
                    $('#dev-publish-modal').prepend('<div class="alert alert-danger no-border"><button data-dismiss="alert" class="close" type="button">' + '<span>×</span><span class="sr-only">Close</span></button>' + data.message + '</div>');
                } else {

                    table.ajax.reload(function () {
                        if (data.status != 'reload-table') {
                            showNotificationMsg(data.message, "", data.status);
                            $('.dev-new-comptetion').html(data.newCount);
                            $('.dev-publish-comptetion').html(data.publishCount);
                            $('.dev-unpublish-comptetion').html(data.unpublishCount);

                        } else {

                            showNotificationMsg(data.message, "", 'error');
                        }
                    }, false)
                }
            }
        }
    });
}