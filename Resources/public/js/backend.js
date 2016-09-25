var callbackFunction;
var cancelCallbackFunction;
var submitButtonProgress;
var mainFormNeedConfirm = false;
var mainFormConfirmed = false;
var submitButtonConfirmFunction;
/**
 * show confirmation box for the user
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param string confimationMessage
 * @param function onConfirmFunction
 * @param string confirmationBoxTitle
 */

//$(document).ready(function(){
//   $('.modal-dialog').modal({
//                show:false,
//        backdrop: true,
//        keyboard: true
//    }).css({
//       'margin-top': function () {
//           return  $(window).height() / 2 -200;
//       }
//    });
//});

function showConfirmationBox(confimationMessage, onConfirmFunction, confirmationBoxTitle, onCancelFunction) {
    var $confirmationModal = $('#confirmationModal');
    if (confirmationBoxTitle) {
        $confirmationModal.find('.modal-title').html(confirmationBoxTitle);
    } else {
        $confirmationModal.find('.modal-title').html(defaultConfirmationMessage);
    }
    $confirmationModal.find('.modal-body').text(confimationMessage);
    $confirmationModal.modal({keyboard: true});
    $confirmationModal.on('shown.bs.modal', function () {
        if(window.location == window.parent.location){
            $confirmationModal.find('button.dev-confirm').focus();
        }
    });
    $confirmationModal.modal('show');
    $confirmationModal.on('hidden.bs.modal', function(e) {
        if (onCancelFunction)
            onCancelFunction();
    });
    callbackFunction = onConfirmFunction;
}

/**
 *
 * @param {type} choicesModalTitle
 * @param {type} choicesModalMessage
 * @param {type} buttons like --> [ { textValue: "Ok", clickAction: "function"},{ text: "Ok", click: "function"},{ text: "Ok", click: "function"} ]
 * @param {type} onCancelFunction [optional]
 */
function showChoicesModal(choicesModalTitle, choicesModalMessage, buttons, onCancelFunction) {
    var $choicesModal = $('#choices-modal');

    $choicesModal.find('.modal-title').html(choicesModalTitle);
    $choicesModal.find('.modal-body').html(choicesModalMessage);


    $choicesModal.find('.modal-footer').html("");
    var $firstButton;
    for (var i in buttons) {
        var btn = buttons[i];
        var attrsString = "";

        for (var key in btn.attrs) {
            var value = btn.attrs[key];
            attrsString += key + '="' + value + '" ';
        }
        var $button = $('<button type="button"' + attrsString + ' onclick="' + btn.clickAction + '">' + btn.textValue + '</button>');
        if(!$firstButton) {
            $firstButton = $button;
        }
        $choicesModal.find('.modal-footer').append($button);
    }
    $choicesModal.modal({keyboard: true});
    $choicesModal.on('shown.bs.modal', function () {
        if($firstButton && window.location == window.parent.location) {
            $firstButton.focus();
        }
    });
    $choicesModal.modal('show');
    $choicesModal.off('hidden.bs.modal');
    $choicesModal.on('hidden.bs.modal', function(e) {
        if (onCancelFunction)
            onCancelFunction();
    });
}

/**
 * show alert box for the user
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param string alertMessage
 * @param string alertBoxTitle
 * @param string onAlertFunction
 */
function showAlertBox(alertMessage, alertBoxTitle, onAlertFunction) {
    var $confirmationModal = $('#alertModal');
    if (alertBoxTitle) {
        $confirmationModal.find('.modal-title').html(alertBoxTitle);
    }
    $confirmationModal.find('.modal-body').html(alertMessage);
    $confirmationModal.modal({keyboard: true});
    $confirmationModal.modal('show');
    $confirmationModal.on('shown.bs.modal', function () {
        if(window.location == window.parent.location){
            $confirmationModal.find('button.dev-alert').focus();
        }
    });
    if (onAlertFunction)
        callbackFunction = onAlertFunction;
    else
        callbackFunction = function() {
        };
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function submitAjaxForm() {
    var formSubmitUrl = $('form.dev-page-main-form').attr('action');
    if (!formSubmitUrl) {
        formSubmitUrl = window.location.href;
    }
  //  var $inputs = $('form.dev-page-main-form').find('*[data-rule-unique]');
   // console.log($inputs)
   // $inputs.trigger('blur');
//   $inputs.blur(function(){
//    var $this = $(this);
//   $this.valid();
//   });
    $('form.dev-page-main-form').attr('ajax-running', 'true');
    $('form.dev-page-main-form').ajaxSubmit({
        url: formSubmitUrl,
        success: function(data){
            if(typeof data != 'object') {
            var form = $(data).find('.dev-page-main-form');
            var messages = $(data).find('.alert-success.remove-5s,.alert-danger.remove-5s');
            $('.alert.remove-5s').remove();
            $('#leftSide .dev-page-main-form').replaceWith(form);
            $('#leftSide').prepend(messages);

            initializePlugins();
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
                    submitButtonProgress = 1;
                } else {
                    $('body').trigger('onFailSubmitForm');
                    submitButtonProgress = 0;
                    document.submitBtns._stop(-1);
                }
            }
        },
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    submitButtonProgress = (evt.loaded / evt.total) / 2;
                }
            }, false);

            xhr.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    submitButtonProgress = (percentComplete / 2) + .5;
                }
            }, false);

            return xhr;
        }
    });
}
/**
 * show a full page loader
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function showLoader() {
    $('.loading').show();
    $('body').addClass('modal-open');
}
/**
 * hide the full page loader
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function hideLoader() {
    $('.loading').hide();
    $('body').removeClass('modal-open');
}


function initSelect2() {
    $('select.select2-without-search').select2({
        minimumResultsForSearch: -1
    });
    $('select.select2-deselect').select2({
        allowClear: true
    });
    $('select.select2-deselect-without-search').select2({
        allowClear: true,
        minimumResultsForSearch: -1
    });
    $('select.select2').select2();
    $('select.select2[readonly]').select2('readonly', true);
    if(typeof chartArea != 'undefined')
        chartArea();
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

function existingImage() {
    $('.fileupload').each(function () {
        var $this = $(this);
        var removeBtn = $this.find('.dev-btn-fileupload-remove');
        var originalImg = $this.find('.fileupload-new.thumbnail img');
        var isCoverPhoto = $this.find('[data-cover]').length > 0 ? true : false;
        if ((originalImg.attr('src').indexOf('avatar') == -1 && originalImg.attr('src').indexOf('/profile') == -1 && originalImg.attr('src').indexOf('/default.jpg') == -1) && removeBtn.is(':hidden')) {
            removeBtn.show();
            removeBtn.click(function () {
                if ((originalImg.attr('src').indexOf('avatar') == -1 && originalImg.attr('src').indexOf('/profile') == -1 && originalImg.attr('src').indexOf('/default.jpg') == -1) && $this.find('.fileupload-preview.thumbnail:eq(1) img').length <= 0 && typeof deleteImageUrl != "undefined") {
                    showConfirmationBox(listMessages.deleteImage, function () {
                        var previewImageUrl = avatarImageUrl;
                        var deleteUrl = deleteImageUrl;
                        if (isCoverPhoto) {
                            deleteUrl = deleteCoverImageUrl;
                            previewImageUrl = coverImageUrl;
                        }
                        $.ajax({
                            url: deleteUrl,
                            method: 'post',
                            success: function (data) {
                                $this.find('.dev-btn-fileupload-remove').removeAttr('style');
                                $this.find('.fileupload-new img:eq(0)').attr('src', previewImageUrl);
                            }
                        });
                    }, listMessages.DeleteImageTitle);
                }
            });
        }
    });
}

function advanceSearch(advanceUrl) {
  //  var url = window.location.pathname;
    var parameters = window.location.search;
    var query = '';

    if (parameters &&  window.location.href.indexOf("advance")!= -1) {
        query = parameters.substring(1);
        query = window.location.search.replace(/&page=(\d+)/, '');
        query = query.replace(/(&|\?)limit=(\d+)/, '');
        query = query.replace(/(&|\?)direction=(\w+)/, '');
        query = query.replace(/(&|\?)sort=(\w+)/, '');
        query = query.replace(/^\&/, '');
        query = query.replace(/^\?/, '');
    }
    if (query) {
        query = '?' + query;
    }


    window.location.href = advanceUrl + query;
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function initializePlugins() {
    if ($('.dev-datepicker').length > 0) {
        $('.dev-datepicker').prop('type', 'text').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            clearBtn: true
        }).on('changeDate', function() {
            $(this).trigger('keyup');
        });
    }
}

jQuery(document).ready(function($) {
//    $(".fancybox").fancybox({
//        openEffect: 'elastic',
//        closeEffect: 'elastic',
//        nextEffect: 'fade',
//        prevEffect: 'fade',
//        tpl: {
//            next: '<a title="" class="fancybox-nav fancybox-next"><span></span></a>',
//            prev: '<a title="" class="fancybox-nav fancybox-prev"><span></span></a>'
//        }
//
//    });

    $('body').on('closeIframeIfExist', function () {
        if ($(window.parent.document).find('#iframeModal').length > 0 && $(window.parent.document).find('#iframeModal').hasClass('in')) {
//           $(window.parent.document).find('#iframeModal').modal('hide');
            $(window.parent.document).find('#iframeModal .close').click();
        }
    });

if($('.fileupload-new.thumbnail img').length > 0){
        existingImage();
        $('body').on('ajaxCallback',function(){
            existingImage();
        });
    }

    ajaxSubmitClickHandler = function(e) {
        $('body').trigger('preAjaxCallback',e);
        if(typeof cityCollection !='undefined' && cityCollection=='city'){
            document.submitBtns = new ProgressButton(e.currentTarget, {
            statusTime: 800,
            callback: function(instance) {
                $('.remove-5s').slideUp();
                if ($('form.dev-page-main-form').attr('data-form-valid') === 'true') {
                    submitButtonProgress = 0;
                    var interval = setInterval(function() {
                        instance._setProgress(submitButtonProgress);
                        if (submitButtonProgress === 1) {
                            instance._stop(1);
                            clearInterval(interval);
                        }
                    }, 200);
                } else {
                    instance._stop(-1);
                }
            }
        });
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
    $('button.progress-button:not(.dev-ignore-progress)').each(function() {
        document.submitBtns = new ProgressButton(this, {
            statusTime: 800,
            callback: function(instance) {
                $('.remove-5s').slideUp();
                if ($('form.dev-page-main-form').attr('data-form-valid') === 'true') {
                    submitButtonProgress = 0;
                    var interval = setInterval(function() {
                        instance._setProgress(submitButtonProgress);
                        if (submitButtonProgress === 1) {
                            instance._stop(1);
                            clearInterval(interval);
                        }
                    }, 200);
                } else {
                    instance._stop(-1);
                }
            }
        });
    }).click(ajaxSubmitClickHandler);

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
    // the auto hide of the flash messages
//    $('.remove-5s').livequery(function() {
//        var $this = $(this);
//        setTimeout(function() {
//            $this.slideUp({
//                complete: function() {
//                    $this.remove();
//                }
//            });
//        }, 5000);
//    });
    // the captcha refresh js
    $('.genmu-captcha-refresh').click(function(e) {
        e.preventDefault();
        $(this).parent().find('.genmu-captcha-image').attr('src', captchaUrl + '?' + Math.random());
    });
    // datepicker remove date icon and hide widget
    $(document).on('click', '.dev-close-calender', function(){
        $('.ui-datepicker').datepicker('hide');
        $(this).prev().val('');
    });

    initializePlugins();

    $(document).on('click', '.dev-close-datepicker', function () {
        var $input = $(this).parent('.dev-datepicker-container').find('input.dev-datepicker');
        if (!$input.is(':disabled')) {
            $input.val('').trigger('keyup').datepicker('hide');
        }
    });

    $(document).on('click', '.dev-open-datepicker', function () {
        var $input = $(this).parent('.dev-datepicker-container').find('input.dev-datepicker');
        if (!$input.is(':disabled')) {
            $input.datepicker('show');
        }
    });

    $(document).on('click','.switch-div .switch',function(){
        var on = !$(this).hasClass("switchOff");
        $(this).toggleClass("switchOff",on);
        $(this).parents(".switch-div").toggleClass("switch-divOff",on);
    });

});