var table;
var back = false;

function intializeTable() {

    table = $('.datatable-column-search-inputs').DataTable({
        "sPaginationType": "full_numbers",
        "bLengthChange": true,
        "iDisplayLength": limit,
//        pageLength: 2,
        iDisplayStart: start,
        "destroy": true,
        'sAjaxSource': ajaxData,
        "bRetrieve": true,
        "deferLoading": totalNumber,
        "bJQueryUI": false,
        "aLengthMenu": [2, 10, 20, 50],
        "bServerSide": true,
        "bPaginate": true,
        "deferRender": true,
        "initComplete": function (settings, json) {
        },
//        "fnPreDrawCallback": function (oSettings) {
//            var pg_size_changed = oSettings._iDisplayLength != self.selectedPageLength;
//            if (pg_size_changed) {
//                oSettings._iDisplayStart = 0; // Reset and go back to the first page
//            }
//        },
//        "fnDrawCallback": function (oSettings) {
//            self.selectedPageLength = oSettings._iDisplayLength; //Storing this for use by fnPreDrawCallback
//        },
        'fnServerData': function (sSource, aoData, fnCallback)
        {
            var sorting = table.order();
            var order = sorting[0];
            var columndir = order[1];
            var columnName = $(table.column(order[0]).header()).html().trim();
            var page = parseInt(table.page(), 10) + parseInt(1, 10);
            var url = ajaxData + '?page=' + page + '&sort=' + columnName + '&columnDir=' + columndir + '&limit=' + table.page.info().length;
            if (!back) {
                pushNewState(null, null, url);
            } else {
                url = window.location.href;
            }
            back = false;
            aoData.push({"name": "from_date", "value": $("#from").val()},
            {"name": "to_date", "value": $("#to").val()});
            $.ajax
                    ({
                        'dataType': 'json',
                        'url': url,
                        beforeSend: function () {
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
                        },
                        'success': function (json) {
                            fnCallback(json)
                            $('input[type=checkbox]').closest('td').addClass('text-center');
                            setTimeout(function () {
                                $('input').uniform();
                                $('div.panel-flat').unblock();


                            }, 200)
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
//                                                            drawCallback: function () {
//                                                                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').addClass('dropup');
//                                                            },
//                                                            preDrawCallback: function () {
//                                                                $(this).find('tbody tr').slice(-3).find('.dropdown, .btn-group').removeClass('dropup');
//                                                            },
        "order": [[1, 'desc']],
    })
}


function pushNewState(data, title, url) {
    stateChanged = true;
    history.pushState(data, title, url);
}



function saveListSelectedColumns(basicModal, url) {
    //modified to use this way instead of form serialize to fix this bug #3535:
    var str = "";
    $('.dev-columns-multi-select input:checked').each(function () {
        str += "columns[]=" + $(this).val() + "&";
    });

    $.ajax({
        url: url,
        method: 'POST',
        data: str,
        success: function (data) {
            // reload page
            basicModal.hide();
            start= table.page.info().start;
            console.log(data)
            table.clear()
            table.destroy();

            columns1 = data.column;
            back = false;

            var th = '<th class="text-center sorting_disabled" id="dev-checkbox"> <div class="form-group">'
                    + '<label class="checkbox-inline"> <input type="checkbox" class="styled"  >'
                    + ' </label>  </div>          </th>'
            $.each(columns1, function (key, column) {
                if (column.data != 'id') {
                    th += '<th data-orderable=' + column.orderable + '>' + column.data + '</th>'
                }

            })
            console.log(th)
            callBack = true
            table.
                    $('.datatable-column-search-inputs thead tr').remove()
            $('.datatable-column-search-inputs thead').html('<tr>' + th + '</tr>')
            console.log($('.datatable-column-search-inputs thead').html())
            table = $('.datatable-column-search-inputs').DataTable({
                "columnDefs": [{
                        "targets": 0,
                        "orderable": false
                    }],
                "sPaginationType": "full_numbers",
                "bLengthChange": true,
                "iDisplayLength": limit,
//        pageLength: 2,
//                iDisplayStart: start,
                "destroy": true,
                'sAjaxSource': ajaxData,
                "bRetrieve": true,
                "deferLoading": totalNumber,
                "bJQueryUI": false,
                "aLengthMenu": [2, 10, 20, 50],
                "bServerSide": true,
                "bPaginate": true,
                "deferRender": true,
                "initComplete": function (settings, json) {
//                    $('#dev-checkbox').removeClass('sorting_asc').addClass('sorting_disabled')
                    $(".dataTables_length select").select2({
                        /* select2 options, as an example */
                        minimumResultsForSearch: -1,
                        width: 'auto'
                    });
                },
                'fnServerData': function (sSource, aoData, fnCallback)
                {
                    if (!callBack) {
                        var sorting = table.order();
                        var order = sorting[0];
                        var columndir = order[1];
                        var columnName = $(table.column(order[0]).header()).html().trim();
                        var page = parseInt(table.page(), 10) + parseInt(1, 10);
                        var url = ajaxData + '?page=' + page + '&sort=' + columnName + '&columnDir=' + columndir + '&limit=' + table.page.info().length;
                        if (!back) {
                            pushNewState(null, null, url);
                        }
                    } else {
                        var url = window.location.href;

                    }
                    back = false;
                    callBack = false;
                    aoData.push({"name": "from_date", "value": $("#from").val()},
                    {"name": "to_date", "value": $("#to").val()});
                    $.ajax
                            ({
                                'dataType': 'json',
                                'url': url,
                                beforeSend: function () {
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
                                },
                                'success': function (json) {
                                    fnCallback(json)
                                    $('input[type=checkbox]').closest('td').addClass('text-center');
                                    setTimeout(function () {
                                        $('input').uniform();
                                        $('.Roleselect').select2({
                                            width: 100
                                        });
                                        $('div.panel-flat').unblock();


                                    }, 200)
                                }
                            });


                },
                columns: columns1

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
                "order": [[1, 'desc']],
            })



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


    /* Binding events */
    var thisObject = this;


    $('div.panel-flat').on('click', '.dev-change-columns', function () {
        thisObject.showColumnOptionsModal();
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
                if (data.status == 'failed-reload') {
                    thisObject.hide();
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
$(document).ready(function () {
//    pushNewState(null, null, window.location.href);

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

    var buttons = document.querySelectorAll('.switchery-primary');
    for (var i = 0, buttonsLength = buttons.length; i < buttonsLength; i++) {
        new Switchery(buttons[i], {color: toggleButtonColor});
    }

    // Select2 selects
    $('.Roleselect').select2({
        width: 100
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

});