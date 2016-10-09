var callbackFunction;
var cancelCallbackFunction;
var submitButtonProgress;
var mainFormNeedConfirm = false;
var mainFormConfirmed = false;
var submitButtonConfirmFunction;



var stack_custom_top = {"dir1": "down", "dir2": "right", "push": "top", "spacing1": 1};


/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function submitAjaxForm() {
    var cur_value = 1,
        progress;

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
            width: "100%",
            cornerclass: "no-border-radius",
            stack: stack_custom_top,
            before_open: function(PNotify) {
                progress = PNotify.get().find("div.progress-bar");
                progress.width(cur_value + "%").attr("aria-valuenow", cur_value).find("span").html(cur_value + "%");


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
            var form = $(data).find('.dev-page-main-form');
            var messages = $(data).find('.alert-success.remove-5s,.alert-danger.remove-5s');
            $('.alert.remove-5s').remove();
                $('.content-wrapper .dev-page-main-form').replaceWith(form);
                $('.content-wrapper .panel-body:first').prepend(messages);


//            initializePlugins();
            initFormValidation();
//            target_admin.init();
//            initSelect2();
            $('body').trigger('ajaxCallback');
            if ($(data).find('.alert-success.remove-5s').length > 0) {
                $('body').trigger('closeIframeIfExist');
            }

//            scrollToFirstNotification();
            } else {
                $('body').trigger('ajaxJsonResponseCallback');
            }
        },
        complete: function(jqxhr) {
            if (jqxhr.status === 403) {
                window.location = accessDeniedUrl;
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
                    progress.width( "100%").attr("aria-valuenow", 100).find("span").html("100%");
 loader.remove();
                } else {
                    $('body').trigger('onFailSubmitForm');
                    submitButtonProgress = 0;
                     loader.remove();
//                    document.submitBtns._stop(-1);
                }
            }
        },
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    submitButtonProgress = (evt.loaded / evt.total) / 2;
                    progress.width( submitButtonProgress*100+"%").attr("aria-valuenow", submitButtonProgress*100).find("span").html(submitButtonProgress*100+"%");

                }
            }, false);

            xhr.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    submitButtonProgress = (percentComplete / 2) + .5;
                    progress.width( submitButtonProgress*100+"%").attr("aria-valuenow", submitButtonProgress*100).find("span").html(submitButtonProgress*100+"%");

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
        if(typeof cityCollection !='undefined' && cityCollection=='city'){
//            document.submitBtns = new ProgressButton(e.currentTarget, {
//            statusTime: 800,
//            callback: function(instance) {
//                $('.remove-5s').slideUp();
//                if ($('form.dev-page-main-form').attr('data-form-valid') === 'true') {
//                    submitButtonProgress = 0;
//                    var interval = setInterval(function() {
//                        instance._setProgress(submitButtonProgress);
//                        if (submitButtonProgress === 1) {
//                            instance._stop(1);
//                            clearInterval(interval);
//                        }
//                    }, 200);
//                } else {
//                    instance._stop(-1);
//                }
//            }
//        });
    }
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
            if (mainFormNeedConfirm) {
                if (!mainFormConfirmed) {
                    document.submitBtns._enable();
                    submitButtonConfirmFunction($this);
                    return;
                }
                mainFormConfirmed = false;
            }
            submitAjaxForm();
        } else {
            $('form.dev-page-main-form').removeAttr('data-force-unique-valid');
            $('[data-remove-color]').removeAttr('data-remove-color');
            $('form.dev-page-main-form').attr('data-form-valid', 'false');
            $('body').trigger('postInvalidAjaxCallback');
            if(typeof $('#news-accordion').val() !== 'undefined'){
              $('body').trigger('openAccordion');
            }
            scrollToFirstNotification();
        }
    };


    // the ajax submit of all forms
//    $('input[type="text"]:visible:enabled:first').focus();
    $('body').on('submit', 'form.dev-page-main-form', function(e) {
        e.preventDefault();
        if ($(this).find('[type="submit"]').length < 1) {
            $('button.progress-button').click();
        }
    });

    // the submit button progress plugin
//    $('button.progress-button:not(.dev-ignore-progress)').each(function() {
//        document.submitBtns = new ProgressButton(this, {
//            statusTime: 800,
//            callback: function(instance) {
//                $('.remove-5s').slideUp();
//                if ($('form.dev-page-main-form').attr('data-form-valid') === 'true') {
//                    submitButtonProgress = 0;
//                    var interval = setInterval(function() {
//                        instance._setProgress(submitButtonProgress);
//                        if (submitButtonProgress === 1) {
//                            instance._stop(1);
//                            clearInterval(interval);
//                        }
//                    }, 200);
//                } else {
//                    instance._stop(-1);
//                }
//            }
//        });
//    }).click(ajaxSubmitClickHandler);

  $('.dev-form-save-button').on('click', function () {
//        $('.dev-main-form-container form').submit();
ajaxSubmitClickHandler();
    });
    // select2 plugin initialization
//    initSelect2();
    // the confirmation modal button click listener
    $('#confirmationModal .dev-confirm').click(function() {
        $('#confirmationModal').modal('hide');
        callbackFunction();
    });
    // the alert modal button click listener
    $('.dev-alert').click(function() {
        $('#alertModal').modal('hide');
        callbackFunction();
    });

    // datepicker remove date icon and hide widget
    $(document).on('click', '.dev-close-calender', function(){
        $('.ui-datepicker').datepicker('hide');
        $(this).prev().val('');
    });

//    initializePlugins();


});