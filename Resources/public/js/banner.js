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
        stack: {"dir1": "down", "dir2": "right", "firstpos1": 0, "firstpos2": 0}
    });
}




function refreshImages() {
    $.ajax({
        url: refreshImagesUrl,
        success: function (data) {
            if (data.status == 'login') {
                window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
            } else {

                if (data.bannerPhoto) {
                    var media = data.bannerPhoto;
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/' + media.imageUrl)
                            .replace(/%image-id%/g, media.id)
                            .replace(/%name%/g, 'bannerPhoto')
                            .replace(/%arabicName%/g, imageErrorMessages.bannerPhoto)
                            .replace(/%errorDimission%/g, imageErrorMessages.BannerimageDimension)
                            .replace(/%image-delete-url%/g, media.deleteUrl)
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                            .replace(/%deleteButton%/g, deleteButton.replace(/%pop-block%/g, media.pop).replace(/%image-delete-url%/g, media.deleteUrl).replace(/%image-id%/g, media.id))
                    $('#dev-bannerPhoto').closest('tr').replaceWith(temepelate);
                    $('[data-popup="popover"]').popover();


                    // Tooltip
                    $('[data-popup="tooltip"]').tooltip({
                        trigger: 'hover'
                    });

                } else {
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                            .replace(/%image-id%/g, '')
                            .replace(/%name%/g, 'bannerPhoto')
                            .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, 'bannerPhoto'))
                            .replace(/%errorDimission%/g, imageErrorMessages.BannerimageDimension)
                            .replace(/%arabicName%/g, imageErrorMessages.bannerPhoto)
                            .replace(/%cropButton%/g, '')
                            .replace(/%deleteButton%/g, '');
                    $('#dev-bannerPhoto').replaceWith(temepelate);
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


var uploadpopup = false;
$(document).ready(function () {


    $("form.form-horizontal").data("validator").settings.ignore = [];



    $(document).on('click', '.upload-image-modal-open', function () {
        if (uploadpopup) {
            uploadpopup = false;
            return false;
        }
        uploadpopup = true;
        $('input.cropit-image-input').val('');
        $('.cropit-preview').removeClass('cropit-image-loaded');
        $('.cropit-preview-image').attr('src', '');
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
        minZoomstring: 'fill',
        imageBackground: true,
        imageBackgroundBorderWidth: 30,
        onImageLoaded: function () {
            if (type == 'upload') {
                $('.dev-submit-image').attr('data-url', uploadUrl)
                $('.dev-submit-image').attr('data-id', '')
            } else if (type == 'crop') {
                $('.dev-submit-image').attr('data-url', element.attr('data-crop-url'))
                $('.dev-submit-image').attr('data-id', element.attr('id'))

            }
            var elementDimessionWidth = 1170;
            var elementDimessionHeight = 600;
            var errorDimension = imageErrorMessages.imageDimension;
            if ($(element).closest('td').attr('data-name') == 'bannerPhoto') {
                elementDimessionWidth = 300;
                elementDimessionHeight = 100;
                errorDimension = imageErrorMessages.BannerimageDimension;
            }
            var elementObject = $('#uploadImg .cropit-image-input');
            if (elementObject.val()) {

                var value = elementObject.val(),
                        file = value.toLowerCase(),
                        extension = file.substring(file.lastIndexOf('.') + 1);

                if ($.inArray(extension, ['jpeg', 'jpg', 'png']) == -1) {
                    showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');

                } else if (elementObject.attr('data-size') > (4 * 1024 * 1024)) {

                    showNotificationMsg(imageErrorMessages.sizeError, "", 'error');

                } else if ($('#image-cropper-modal').cropit('imageSize').width < elementDimessionWidth || $('#image-cropper-modal').cropit('imageSize').height < elementDimessionHeight) {

                    showNotificationMsg(errorDimension, "", 'error');

                } else {

                    $('#uploadImg').modal('show');
                }
            }

        }, onImageError: function () {
            $('#uploadImg').modal('hide');
            showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
        },
        onFileReaderError: function () {
            $('#uploadImg').modal('hide');
            showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
        }

    })
    $('#image-cropper-modal').cropit('previewSize', {width: 585, height: 300});
    $('#image-cropper-modal').cropit('exportZoom', 2);

    $(document).on('click', '.dev-submit-image', function () {
        var imageFile = $('#image-cropper-modal').cropit('export')
        var formData = new FormData();
        formData.append("media[file]", imageFile);
        $('.dev-crop-spinner').show();
        $('.dev-submit-image').hide();
        $.ajax({
            url: $(this).attr('data-url') + '?imageType=' + name,
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
                    console.log(name)
                    var errorDimission = imageErrorMessages.imageDimension;
                    if (name == 'bannerPhoto') {
                        errorDimission = imageErrorMessages.BannerimageDimension;
                    }
                    var media = data.media;
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/' + media.imageUrl)
                            .replace(/%image-id%/g, media.id)
                            .replace(/%name%/g, name)
                            .replace(/%image-delete-url%/g, media.deleteUrl)
                            .replace(/%arabicName%/g, imageErrorMessages[name])
                            .replace(/%errorDimission%/g, errorDimission)
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                            .replace(/%deleteButton%/g, deleteButton.replace(/%pop-block%/g, media.pop).replace(/%image-delete-url%/g, media.deleteUrl).replace(/%image-id%/g, media.id))
                    element.closest('tr').replaceWith(temepelate);
                    showNotificationMsg(data.message, "", data.status);
                    $('#uploadImg').modal('hide');
                    $('[data-popup="popover"]').popover();


                    // Tooltip
                    $('[data-popup="tooltip"]').tooltip({
                        trigger: 'hover'
                    });

                } else {
                    $('#uploadImg').modal('hide');
                    showNotificationMsg(imageErrorMessages.generalError, "", 'error');
                    refreshImages();
                }
                $('.dev-crop-spinner').hide();
                $('.dev-submit-image').show();
            }

        });
    });

    $(document).on('click', '.dev-delete-btn', function (e) {
        var $this = $(this);
        var closestTr = $this.parents('[role="tooltip"]').prev().closest('tr');
        var closestTd = $this.parents('[role="tooltip"]').prev().closest('td');
        tdLoadingToggle(closestTd);
        $.ajax
                ({
                    'dataType': 'json',
                    'url': $this.parents('[role="tooltip"]').prev().data('href'),
                    'success': function (data) {
                        var errorDimission = imageErrorMessages.imageDimension;
                        if (data.status == 'login') {
                            window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
                        }
                        else if (data.type == 'success') {
                            if ((closestTr.attr('id')).replace(/dev-/g, '') == 'bannerPhoto') {
                                errorDimission = imageErrorMessages.BannerimageDimension;
                            }
                            var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                                    .replace(/%image-id%/g, '')
                                    .replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, ''))
                                    .replace(/%arabicName%/g, imageErrorMessages[(closestTr.attr('id')).replace(/dev-/g, '')])
                                    .replace(/%errorDimission%/g, errorDimission)
                                    .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, '')))
                                    .replace(/%cropButton%/g, '')
                                    .replace(/%deleteButton%/g, '');
                            closestTr.replaceWith(temepelate);
                            showNotificationMsg(data.message, "", data.status);

                        } else {
                            showNotificationMsg(imageErrorMessages.generalError, "", 'error');
                            refreshImages();
                        }
                        tdLoadingToggle(closestTd);
                    }
                });
    })
    $(document).on('click', '.dev-crop-images', function () {
//        type = 'crop';
//        name = $(this).closest('td').attr('data-name');
//        element = $(this);
//        var imageSrc = $(this).closest('tr').find('img:first').attr('src')
//        $('#image-cropper-modal').cropit('imageSrc', imageSrc);

        $('input.cropit-image-input').val('');
        $('.cropit-preview').removeClass('cropit-image-loaded');
        $('.cropit-preview-image').attr('src', '');
        name = $(this).closest('td').attr('data-name');
        element = $(this);
        type = 'crop';
        $('#uploadImg .cropit-image-input').click();
//        $('#uploadImg').modal('show');

    })
    // Handle rotation
    $(document).on('click', '.rotate-cw-btn', function () {
        $('#image-cropper-modal').cropit('rotateCW');
    });
    $(document).on('click', '.rotate-ccw-btn', function () {
        $('#image-cropper-modal').cropit('rotateCCW');
    });



    $(document).on('onFailSubmitForm', function () {
        refreshImages();
    })

})