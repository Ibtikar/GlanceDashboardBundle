function showNotificationMsg(title, text, type) {

    var notificationIcons = {
        success: {
            icon: "icon-checkmark3",
            class: "bg-success"
        },
        info: {
            icon: "icon-info22",
            class: "bg-info"
        },
        error: {
            icon: "icon-blocked",
            class: "bg-danger"
        }

    };

    type = (typeof type == "undefined" ? "success" : type);

    new PNotify({
        title: title,
        text: text,
        icon: notificationIcons[type]['icon'],
        addclass: notificationIcons[type]['class'],
        type: type,
        buttons: {
            sticker: false
        },
        stack:  {"dir1": "down", "dir2": "right", "firstpos1": 0, "firstpos2": 0}

    });
}


function refreshImages(){
    $.ajax({
            url:refreshImagesUrl,
            success: function (data) {
                if(data.status=='login'){
                            window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
                }else{
                if (data.coverPhoto) {
                    var media = data.coverPhoto;
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/' + media.imageUrl)
                            .replace(/%image-id%/g, media.id)
                            .replace(/%name%/g, 'coverPhoto')
                            .replace(/%arabicName%/g, imageErrorMessages.coverPhoto)
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                    $('#dev-coverPhoto').closest('tr').replaceWith(temepelate);
                    $('[data-popup="popover"]').popover();

                    $('#form_defaultCoverPhoto').val(media.id)

                    // Tooltip
                    $('[data-popup="tooltip"]').tooltip({
                        trigger: 'hover'
                    });

                }else{
                     var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                                    .replace(/%image-id%/g, '')
                                    .replace(/%name%/g, 'coverPhoto')
                                    .replace(/%arabicName%/g, imageErrorMessages.coverPhoto)
                                    .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, 'coverPhoto'))
                                    .replace(/%cropButton%/g, '');
                             $('#dev-coverPhoto').replaceWith(temepelate);
                }

            }
        }


        })
}

function tdLoadingToggle(elm){
    var loaderDiv = '<div class=" btn dev-spinner-container" disabled=""><i class="spinner icon-spinner"></i></div>';
    if(!$(elm).is('td')){
        elm = $(elm).parent('td');
    }
    if($(elm).length > 0) {
       if($(elm).find('.dev-spinner-container').length > 0){
           $(elm).find('.dev-spinner-container').remove();
           $(elm).children().show();
       }else{
           $(elm).children().hide();
           $(elm).append(loaderDiv);
       }
    }
}
var uploadpopup=false;
$(document).ready(function () {

    $("form.form-horizontal").data("validator").settings.ignore = [];


    $(document).on('click','.upload-image-modal-open',function () {
        if(uploadpopup){
            uploadpopup=false;
            return false;
        }
        uploadpopup=true;
        $('input.cropit-image-input').val('');
        $('.cropit-preview').removeClass('cropit-image-loaded');
        $('.cropit-preview-image').attr('src','');
        name = $(this).closest('td').attr('data-name');
        element = $(this);
        type = 'upload';
        $('#uploadImg .cropit-image-input').click();
        if (cropperPluginInitialized) {
            return;
        }

        cropperPluginInitialized = true;
    });

    $('.image-editor').cropit({smallImage: 'allow',
        imageState: {
//            src: '/images/cover.jpg'
        }
    });



    $('#image-cropper-modal').cropit({
        smallImage: 'stretch',
        minZoomstring:'fill',
        imageBackground: true,
        imageBackgroundBorderWidth: 30,
        onImageLoaded: function () {
            if (type == 'upload') {
                $('.dev-submit-image').attr('data-url', uploadUrl)
                $('.dev-submit-image').attr('data-id', '')
            } else if (type == 'crop') {
                $('.dev-submit-image').attr('data-url', element.attr('data-crop-url'))
                $('.dev-submit-image').attr('data-id', element.attr('id'))
                uploadUrl=element.attr('data-crop-url');

            }
            var elementObject = $('#uploadImg .cropit-image-input');
            if (elementObject.val()) {

                var value = elementObject.val(),
                        file = value.toLowerCase(),
                        extension = file.substring(file.lastIndexOf('.') + 1);

                if ($.inArray(extension, ['jpeg', 'jpg', 'png', 'gif']) == -1) {
                    showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');

                } else if (elementObject.attr('data-size') > (4 * 1024 * 1024)) {

                    showNotificationMsg(imageErrorMessages.sizeError, "", 'error');

                } else if ($('#image-cropper-modal').cropit('imageSize').width != 819 || $('#image-cropper-modal').cropit('imageSize').height != 1091) {

                    showNotificationMsg(imageErrorMessages.imageDimension, "", 'error');

                } else {
                    uploadImageToServer($('.cropit-preview-image').attr('src'), uploadUrl);


                }
            }

        }, onImageError: function () {
            showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
        },
        onFileReaderError: function () {
            showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
        }

    });

    $(document).on('click', '.dev-submit-image', function () {
        var imageFile = $('#image-cropper-modal').cropit('export');
        uploadImageToServer(imageFile, $(this).attr('data-url'))

    });

    function uploadImageToServer(imageFile, url) {
        var formData = new FormData();
        formData.append("media[file]", imageFile);
        $('.dev-crop-spinner').show();
        $('.dev-submit-image').hide();
        $.ajax({
            url: url + '?imageType=' + name,
            type: 'POST',
            data: formData,
//            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.status == 'login') {
                    window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
                }
                else if (data.status == 'success') {
                    var media = data.media;
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/' + media.imageUrl)
                            .replace(/%image-id%/g, media.id)
                            .replace(/%name%/g, name)
                            .replace(/%image-delete-url%/g, media.deleteUrl)
                            .replace(/%arabicName%/g, imageErrorMessages[name])
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                    element.closest('tr').replaceWith(temepelate);
                    showNotificationMsg(data.message, "", data.status);
                    $('#form_defaultCoverPhoto').val(media.id)
                } else {
                    if (typeof data.message != 'undefined') {
                        showNotificationMsg(data.message, "", 'error');
                    } else {
                        showNotificationMsg(imageErrorMessages.generalError, "", 'error');
                    }

                    refreshImages();
                }
            }

        });
    }

    $(document).on('click', '.dev-crop-images', function () {
//        type = 'crop';
//        name = $(this).closest('td').attr('data-name');
//        element = $(this);
//        var imageSrc = $(this).closest('tr').find('img:first').attr('src')
//        $('#image-cropper-modal').cropit('imageSrc', imageSrc);

        $('input.cropit-image-input').val('');
        $('.cropit-preview').removeClass('cropit-image-loaded');
        $('.cropit-preview-image').attr('src','');
        name = $(this).closest('td').attr('data-name');
        element = $(this);
        type = 'crop';
        $('#uploadImg .cropit-image-input').click();
//        $('#uploadImg').modal('show');

    })
    // Handle rotation
    $(document).on('click','.rotate-cw-btn',function () {
        $('#image-cropper-modal').cropit('rotateCW');
    });
    $(document).on('click','.rotate-ccw-btn',function () {
        $('#image-cropper-modal').cropit('rotateCCW');
    });

    $(document).on('openTab', function () {
        $("form.form-horizontal").data("validator").settings.ignore = [];
        if($('.help-block:eq(0)').closest('.tab-pane').length > 0){
        $('a[href="#'+$('.help-block:eq(0)').closest('.tab-pane').attr('id')+'"').click();
    }
    });

    $(document).on('onFailSubmitForm', function () {
        refreshImages();
    })

})