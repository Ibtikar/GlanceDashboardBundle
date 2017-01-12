var table;
var callBack = false;
var checkbox = false;
var dataTableDefault = {
    "sPaginationType": "full_numbers",
    "bLengthChange": true,
    "iDisplayLength": limit,
    "iDisplayStart": start,
    "destroy": true,
    'sAjaxSource': ajaxData,
    "bRetrieve": true,
    "bJQueryUI": false,
    "aLengthMenu": [10, 20, 50],
    "bServerSide": true,
    "bPaginate": true,
    "deferRender": true,
    "initComplete": function (settings, json) {
    },
    "preDrawCallback": function (settings) {
//        if (checkbox) {
//            checkbox = false;
//            return false;
//
//        }
    },
    'fnServerData': function (sSource, aoData, fnCallback)
    {
         if (!callBack) {
        var sorting = table.order();
        var order = sorting[0];

        if($.isArray(order)){
            var columndir = order[1];
            var columnName = $(table.column(order[0]).header()).attr('data-name').trim();


            } else {
                var columndir = sorting[1];
                var columnName = $(table.column(sorting[0]).header()).attr('data-name').trim();
            }
            var page = parseInt(table.page(), 10) + parseInt(1, 10);
            var url = ajaxData + '?page=' + page + '&sort=' + columnName + '&columnDir=' + columndir + '&limit=' + table.page.info().length;
            if (typeof parameterNotRemoved != 'undefined') {
                url += '&' + parameterNotRemoved;
            }
            pushNewState(null, null, url);
        }
        else {
            if (typeof parameterNotRemoved != 'undefined') {
                url += window.location.href + '?' + parameterNotRemoved;
            } else {
                url = window.location.href;

            }
        }
            callBack = false;
            $.ajax
                    ({
                        'dataType': 'json',
                        'url': url,
                        beforeSend: function () {
                            blockPage();
                    },
                        'success': function (json) {
                            if (json.columns.length == columns.length) {
                                fnCallback(json)
//                                $('input[type=checkbox]').closest('td').addClass('text-center');
                                setTimeout(function () {
                                    $('input').uniform();
                                    unblockPage()


                                }, 200)
                            } else {
                                reIntaializeTable(json);
                            }

                        }
                    });



    },
    columns: columns

    ,
    dom: '<"datatable-scroll"t><"datatable-footer"lip>',
    language: {
        search: '<span>بحث:</span> _INPUT_',
        lengthMenu: '_MENU_',
        sLengthMenu: "اظهر _MENU_ ",
        sInfo: " _START_ - _END_ من _TOTAL_ ",
        sZeroRecords: "لا يوجد ما تبحث عنه",
        sInfoEmpty: " 0 - 0 من 0 ",
        paginate: {'first': 'الاول', 'last': 'الاخير', 'next': '&larr;', 'previous': '&rarr;'}
    },
    drawCallback: function () {
        $('[data-popup="tooltip"]').tooltip({
            trigger: 'hover'
        });
        $('[data-popup="popover"]').popover({
            delay:{ "hide": 500 }
        });

        if (!columns[0].orderable) {
            $('th:first').removeClass('sorting_asc').addClass('sorting_disabled')
        }
        if ($('.datatable-column-search-inputs input.dev-checkbox').length == $('.datatable-column-search-inputs input:checked.dev-checkbox').length && $('.datatable-column-search-inputs input:checked.dev-checkbox').length != 0) {
            $('.dev-checkbox-all').prop('checked', true).uniform('refresh');
        } else {
            $('.dev-checkbox-all').prop('checked', false).uniform('refresh');
        }
//        $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
    },
};

function intializeTable() {
if(sort){
    table = $('.datatable-column-search-inputs').DataTable($.extend({},dataTableDefault, { "deferLoading": totalNumber,"order": sort}));

}else{
    table = $('.datatable-column-search-inputs').DataTable($.extend({},dataTableDefault, { "deferLoading": totalNumber}));

    }

}

function reIntaializeTable(data) {
    table.clear();
    table.destroy();
    dataTableDefault.columns = data.columns;
    columns = data.columns;
    dataTableDefault.iDisplayStart = table.page.info().start;
    dataTableDefault.iDisplayLength = table.page.info().length;

    callBack = true
    var th = ''
    $.each(data.columns, function (key, column) {
//                if (column.data == 'id') {
//                    th += '<th class="text-center sorting_disabled" id="dev-checkbox"> </th>';
//                } else
//                {
        th += '<th class="' + column.class + '" data-orderable=' + column.orderable + ' data-name="' + column.name + '">' + column.title + '</th>'
//                }

    })
    $('.datatable-column-search-inputs thead tr').remove()
    $('.datatable-column-search-inputs thead').html('<tr>' + th + '</tr>')
    if (data.sort) {
        datatableSetting = $.extend({}, dataTableDefault, {"order": JSON.parse(data.sort), "initComplete": function (settings, json) {
                if (!columns[0].orderable) {
                    $('th:first').removeClass('sorting_asc').addClass('sorting_disabled')
                }
                $(".dataTables_length select").select2({
                    /* select2 options, as an example */
                    minimumResultsForSearch: -1,
                    width: 'auto'
                });
            }})
    } else {
        datatableSetting = $.extend({}, dataTableDefault, {"initComplete": function (settings, json) {
                $(".dataTables_length select").select2({
                    /* select2 options, as an example */
                    minimumResultsForSearch: -1,
                    width: 'auto'
                });
            }});
    }
    table = $('.datatable-column-search-inputs').DataTable(datatableSetting)
}


function pushNewState(data, title, url) {
    stateChanged = true;
    history.pushState(data, title, url);
}
function blockPage() {
    $('div.panel-flat').block({
        message: '<i class="icon-spinner2 spinner"></i>',
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: 0.8,
            cursor: 'wait',
            'box-shadow': '0 0 0 1px #ddd'
        },
        css: {
            border: 0,
            padding: 0,
            backgroundColor: 'none'
        }
    });
}
function unblockPage() {
    $('div.panel-flat').unblock();

}


function saveListSelectedColumns(basicModal, url) {
    //modified to use this way instead of form serialize to fix this bug #3535:
    if($('.dev-save-columns').attr('ajax-running')){
        return;
    }
    $('.dev-save-columns').attr('ajax-running', true)
    $('.dev-save-columns').append('<i class="icon-spinner6 spinner position-right"></i>');
    var str = "";
    $('.dev-columns-multi-select input:checked').each(function () {
        str += "columns[]=" + $(this).val() + "&";
    });

    $.ajax({
        url: url,
        method: 'POST',
        data: str,
        success: function (data) {
//            console.log('hnaa')
            if(data.status=='login'){
                window.location.reload(true);

            } else {
                basicModal.hide();
//                console.log('reinaialize')
                reIntaializeTable(data);
            }
        }
    });
}

/**
 * @returns {BaseList}
 */
function BaseList() {

    this.showColumnOptionsModal = function () {
        var basicModal = new BasicModal();
        basicModal.show(changeListColumnsUrl, function () {
            $(".dev-save-columns").click(function () {
                saveListSelectedColumns(basicModal, changeListColumnsUrl);
            })
        });
    }

    this.showPermisionModal = function (clickedElement) {
        var basicModal = new BasicModal();
        basicModal.show(showPermisionUrl+'?id='+clickedElement.attr("data-id"), function () {
        });
    }


    /* Binding events */
    var thisObject = this;


    $('div.panel-flat').on('click', '.dev-change-columns', function () {
        $('[data-popup="tooltip"]').tooltip("hide");
        blockPage();
        thisObject.showColumnOptionsModal();
    });

   $('div.panel-flat').on('click','.dev-role-getPermision',function(){
    thisObject.showPermisionModal($(this))
    })

    $('div.panel-flat').on('click', '.dev-publish-document', function () {
        $('[data-popup="tooltip"]').tooltip("hide");
        blockPage();
        showDocumentPublishModal($(this));
    });



}


function  showDocumentPublishModal(element) {
    var basicModal = new BasicModal();
    basicModal.show(publisUrl + '?id=' + element.attr('data-id'), function () {
        unblockPage();
        $(".dev-save-publish-location").click(function () {
                saveInpublishLocation(basicModal, publisUrl);
        })
    });
}


function saveInpublishLocation(basicModal, url) {
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
                        } else {
                            showNotificationMsg(data.message, "", 'error');
                        }
                    }, false)
                }
            }
        }
    });
}

/**
 *
 * @author Gehad Mohamed
 * @param text title
 * @param text text
 * @param [success,info,error] type
 * @returns {undefined}
 */
function showNotificationMsg(title,text,type) {

    var notificationIcons = {
        success : {
            icon : "icon-checkmark3",
            class : "bg-success"
        },
        info : {
            icon : "icon-info22",
            class : "bg-info"
        },
        error : {
            icon : "icon-blocked",
            class : "bg-danger"
        }

    };

    type = (typeof type == "undefined"?"success":type);

    new PNotify({
        title: title,
        text: text,
        icon: notificationIcons[type]['icon'],
        addclass: notificationIcons[type]['class'],
        type:type,
        buttons: {
            sticker: false
        },
        stack: {"dir1": "down", "dir2": "right", "firstpos1": 0, "firstpos2": 0}
    });
}

function BasicModal() {
    var thisObject = this;
    this.hideCallback;
    this.show = function (url, callback, params) {
        $('#modal_theme_primary').on('hidden.bs.modal', function () {
            thisObject.hide();
        });
        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            success: function (data) {
                if (data.status == 'reload-table') {
                    $('#modal_theme_primary').modal('hide');
                    table.ajax.reload(function () {
                        showNotificationMsg(data.message, "", 'error');
                    }, false)
                }
                if (data.status == 'failed-reload') {
                    $('#modal_theme_primary').modal('hide');
                    var numOfRecords = $('tr[data-id]').length;
                    var pageNum = getQueryVariable('page');
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    return;
                }
                if (data.status == "error") {
                    showAlertBox(data.message);
                    return;
                }
                var basicModal = $('#modal_theme_primary');
                basicModal.find('.modal-content').html(data);


                $('select.select2').on('select2-close', function () {
                    $('#modal_theme_primary').attr('tabindex', '-1');
                }).on("select2-open", function () {
                    $('#modal_theme_primary').removeAttr('tabindex');
                });
                callback();
                basicModal.modal({keyboard: true})
                basicModal.modal('show');
            }
        });
    }
    this.hide = function () {
        $('#modal_theme_primary .select2').select2('destroy');
        $('#modal_theme_primary').modal('hide');
        if (thisObject.hideCallback !== undefined)
            thisObject.hideCallback();
    }
    this.onHide = function (callback) {
        thisObject.hideCallback = callback;
    }
}

function getQueryVariable(variable) {

    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {

        var pair = vars[i].split("=");

        if (pair[0] == variable) {

            return pair[1];
        }
    }

}

function bulkFunction() {
    $('tr[data-id]').each(function () {
        $(this).removeClass('success').removeClass('danger').find('td:last').html('');
    });

    var $form = $('.dev-bulk-actions-form');
    var formData = $form.serializeArray();

    var numOfRecords = $('tbody .dev-checkbox').length;
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
                $('.dev-document-count').html( data.count)

                table.ajax.reload(function (){
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
            }
        }
    });
}


/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function showBulkActionSelect() {
    var checkedElm = $('tbody .dev-checkbox:checked');
    var checkedElmCount = checkedElm.length;
    if (checkedElmCount > 0) {
        $('[data-replace-title]').each(function(){
            $(this).attr('data-original-title',$(this).attr('data-replace-title').replace('%count%',checkedElmCount));
        });
        $('.dev-bulk-action-container').show();
    } else {
        $('.dev-bulk-action-container').hide();
    }
}

$(document).ready(function () {
    $(".alert.alert-success").fadeTo(5000, 500).slideUp(500, function () {
        $(".alert.alert-success").slideUp(500);
    });

    $('.select').select2();

    $(window).on("popstate", function (e) {
//        if (typeof stateChanged !== "undefined" && stateChanged) {
//            back = true;
//            console.log(e.originalEvent.state)
//            console.log(window.location.href)
        window.location.reload();
//            table.ajax.url(window.location.href).load();
//        }
    });

    $('div.panel-flat').on('click','.dev-checkbox-all',function (e) {
        if ($(this).is(':checked')) {
            $('.datatable-column-search-inputs').find('input.dev-checkbox').prop('checked', true).uniform('refresh');
        } else {
            $('.datatable-column-search-inputs').find('input.dev-checkbox').prop('checked', false).uniform('refresh');

        }
        checkbox=true;
    });

    $('div.panel-flat').on('click', '.dev-checkbox', function (e) {
        if ( $('.datatable-column-search-inputs input:checked.dev-checkbox').length!=0 && $('.datatable-column-search-inputs input.dev-checkbox').length == $('.datatable-column-search-inputs input:checked.dev-checkbox').length ) {
            $('.dev-checkbox-all').prop('checked', true).uniform('refresh');
        } else {
            $('.dev-checkbox-all').prop('checked', false).uniform('refresh');
        }
    });

    $('.panel [data-action=reload]').click(function (e) {
        e.preventDefault();
        table.ajax.reload(function (){
                    showBulkActionSelect();
                }, false)

    });


    $('#advanced-search-Btn').on('click', function () {
        if ($(".advanced-search").hasClass("searchhidden")) {
            $(".advanced-search").slideDown();
            $(".advanced-search").removeClass('searchhidden');
        } else {
            $(".advanced-search").slideUp();
            $(".advanced-search").addClass('searchhidden');
            $(".advanced-search-more").slideUp();
        }

    });


    // advanced-search-more
    $('#advanced-search-more').on('click', function () {
        if ($(".advanced-search-more").hasClass("searchhidden")) {
            $(".advanced-search-more").slideDown();
            $(".advanced-search-more").removeClass('searchhidden');
        } else {
            $(".advanced-search-more").slideUp();
            $(".advanced-search-more").addClass('searchhidden');
            $(".advanced-search-more").slideUp();
        }
    });

    $('.content-wrapper').on('click','.dev-bulk-action-btn',function(e){
        var action = $(this).parents('.popover').prev().data('action');
        if(action){
            $('#dev-bulk-action').val(action);
        }else{
            throw "Missing data-action attribute can't be found in action button";
        }
        bulkFunction();
    });

    $('.dataTables_wrapper').on('click', '.dev-delete-btn', function (e) {
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
                            if (pageNum != 1 && numOfRecords==1) {
                                table.page(parseInt(table.page(), 10) - parseInt(1, 10));
                            }
                        }
                        $('.dev-document-count').html(json.count)
                        showNotificationMsg(json.message, "", status);
                        unblockPage();
                        $('.dev-bulk-action-container').hide();
                        table.ajax.reload(function () {
                            showBulkActionSelect();
                        }, false);
                    }
                });
    });

    $('.content-wrapper').on('change', '.dev-checkbox', showBulkActionSelect);
    showBulkActionSelect();
});
jQuery(document).on('ajaxComplete', function (event, response) {
    if (response) {
//        if(response.status === 0 && detectIE()) {
//            window.location.reload(true);
//        }
        if (response.status === 404) {
            window.location = notFoundUrl;
        }
//        if (response.status === 403) {
//            window.location.reload();
//        }
        if (typeof response.responseJSON === 'object') {
            if (typeof response.responseJSON.status !== 'undefined') {
                handleAjaxResponse(response.responseJSON);
            }
        }
    }
});
function handleAjaxResponse(responseJSON) {

        switch (responseJSON.status) {
            case 'login':
                window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
                break;
            case 'denied':
                window.location = accessDeniedUrl;
                break;
            case 'reload-page':
                window.location.reload(true);
                break;
            case 'redirect':
                window.location = responseJSON.url;
                break;
            case 'notification':
                var hideAfterSeconds = null;
                if (typeof responseJSON.hideAfterSeconds !== 'undefined') {
                    hideAfterSeconds = responseJSON.hideAfterSeconds;
                }
                showNotification(responseJSON.message, responseJSON.type, hideAfterSeconds);
                break;
    }
}
