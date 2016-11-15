var callbackFunction;
var cancelCallbackFunction;
var submitButtonProgress;
var mainFormNeedConfirm = false;
var mainFormConfirmed = false;
var submitButtonConfirmFunction;



var stack_bottom_right = {"dir1": "down", "dir2": "right", "firstpos1": 0, "firstpos2": 0};


/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function submitAjaxForm() {
    var cur_value = 0,
        progressStatus;

        // Make a loader.
        var loader = new PNotify({
            title: "يتم الحفظ",
            text: '<div class="progress progress-striped active" style="margin:0">\
            <div class="progress-bar bg-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0">\
            <span class="sr-only">0%</span>\
            </div>\
            </div>',
            addclass: 'bg-primary',
            icon: 'icon-spinner4 spinner',
            hide: false,
            buttons: {
                closer: false,
                sticker: false
            },
            history: {
                history: false
            },
//            width: "100%",
            cornerclass: "no-border-radius",
            stack: stack_bottom_right,
            before_open: function(PNotify) {
                progressStatus = PNotify.get().find("div.progress-bar");
                progressStatus.width(cur_value + "%").attr("aria-valuenow", cur_value).find("span").html(cur_value + "%");


            }
        });
    var formSubmitUrl = $('form.dev-page-main-form').attr('action');
    if (!formSubmitUrl) {
        formSubmitUrl = window.location.href;
    }

    $('form.dev-page-main-form').attr('ajax-running', 'true');
    $('form.dev-page-main-form').ajaxSubmit({
        url: formSubmitUrl,

        success: function(data){
            if(typeof data != 'object') {
            var form = $(data).find('form.dev-page-main-form');
            var messages = $(data).find('.alert-success,.alert-danger');


            $('.alert').remove();
                $('form.dev-page-main-form').replaceWith(form);
                $('.dev-main-form-container .col-md-12:first').prepend(messages);


//            initializePlugins();
            initFormValidation();
//            target_admin.init();
//            initSelect2();
            $('body').trigger('ajaxCallback');
            if ($(data).find('.alert-success.remove-5s').length > 0) {
                $('body').trigger('closeIframeIfExist');
            }

            scrollToFirstNotification();
            } else {
                $('body').trigger('ajaxJsonResponseCallback');
            }
        },
        complete: function(jqxhr) {
            if (jqxhr.status === 403) {
                window.location.reload();
            }
            var IS_JSON = true;
            try{
                $.parseJSON(jqxhr.responseText);
            }
            catch (err) {
                IS_JSON = false;
            }
            if (!IS_JSON) {
                var state = $(jqxhr.responseText).find('.form-state').val();
                if(state == 'true'){
                    $('body').trigger('onSuccessSubmitForm');
                    progressStatus.width( "100%").attr("aria-valuenow", 100).find("span").html("100%");
                    loader.remove();
                } else {
                    $('body').trigger('onFailSubmitForm');
                    loader.remove();
                }
            }
            $('form.dev-page-main-form').attr('ajax-running', 'false');

        },
        xhr: function () {
            var xhr = new window.XMLHttpRequest();
            // Upload progress
            xhr.upload.addEventListener("progress", function (evt) {

                if (evt.lengthComputable) {
                    var percentComplete = (evt.loaded * 100) / evt.total;
                    progressStatus.width(percentComplete + "%").attr("aria-valuenow", percentComplete / 100).find("span").html(percentComplete + "%");

                }
            }, false);

            // Download progress
            xhr.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                    var percentComplete = (evt.loaded * 100) / evt.total;
                    progressStatus.width(percentComplete + "%").attr("aria-valuenow", percentComplete / 100).find("span").html(percentComplete + "%");
                }
            }, false);

            return xhr;
        }
    });
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

function retunToPreviousPage(pageNumber) {
    var url = window.location.pathname;
    var num=parseInt(pageNumber)-1;
//    console.log(num);
//    console.log(window.location.pathname + window.location.search.replace(/page=(\d+)/, 'page=' + num));
    url = window.location.pathname + window.location.search.replace(/page=(\d+)/, 'page=' + num);
    pushNewState(null, null, url);
    ajaxUpdateTable(url, {elm: $('#bulk-form')}, function() {
        target_admin.init();
        initSelect2();
    });
}



jQuery(document).ready(function($) {

    $('body').on('closeIframeIfExist', function () {
        if ($(window.parent.document).find('#iframeModal').length > 0 && $(window.parent.document).find('#iframeModal').hasClass('in')) {
//           $(window.parent.document).find('#iframeModal').modal('hide');
            $(window.parent.document).find('#iframeModal .close').click();
        }
    });


    ajaxSubmitClickHandler = function(e) {
        $('body').trigger('preAjaxCallback',e);

        var $this = $(this);
        $('form.dev-page-main-form').attr('data-force-unique-valid', 'true');
            if (typeof CKEDITOR == 'object') {
//                if(typeof comics =='undefined'){
//                    CK_jQ();
//                }
//                else{
                    for (instance in CKEDITOR.instances) {
                        CKEDITOR.instances[instance].updateElement();
                    }
//                }
            }
        if ($('form.dev-page-main-form').valid()) {
            $('form.dev-page-main-form').attr('data-form-valid', 'true');
//            if (mainFormNeedConfirm) {
//                if (!mainFormConfirmed) {
//                    document.submitBtns._enable();
//                    submitButtonConfirmFunction($this);
//                    return;
//                }
//                mainFormConfirmed = false;
//            }
            submitAjaxForm();
        } else {
            $('form.dev-page-main-form').removeAttr('data-force-unique-valid');
            $('[data-remove-color]').removeAttr('data-remove-color');
            $('form.dev-page-main-form').attr('data-form-valid', 'false');
            $('body').trigger('postInvalidAjaxCallback');
            $('body').trigger('openTab');
//            scrollToFirstNotification();
        }
    };


    // the ajax submit of all forms
    $('body').on('submit', 'form.dev-page-main-form', function(e) {
        e.preventDefault();
        if ($(this).find('[type="submit"]').length < 1) {
            $('button.dev-form-submit-btn').click();
        }
    });



  $('.dev-form-submit-btn').on('click', function () {
    if(typeof  $('form.dev-page-main-form').attr('ajax-running') == 'undefined' || $('form.dev-page-main-form').attr('ajax-running') !='true') {
        ajaxSubmitClickHandler();

    }
    });

jQuery(document).on('ajaxComplete', function (event, response) {
    if (response) {
//        if(response.status === 0 && detectIE()) {
//            window.location.reload(true);
//        }
        if (response.status === 404) {
            window.location = notFoundUrl;
        }
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


});