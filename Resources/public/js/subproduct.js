var RelatedRecipeObj = [];
var RelatedArticleObj = [];
var RelatedTipObj = [];
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
                $('#media-list-target-right').html('');
                if (data.profilePhoto) {
                    var media = data.profilePhoto;
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/' + media.imageUrl)
                            .replace(/%image-id%/g, media.id)
                            .replace(/%name%/g, 'profilePhoto')
                            .replace(/%arabicName%/g, imageErrorMessages.profilePhoto)
                            .replace(/%errorDimission%/g, imageErrorMessages.imageDimension)
                            .replace(/%image-delete-url%/g, media.deleteUrl)
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                            .replace(/%deleteButton%/g, deleteButton.replace(/%pop-block%/g, media.pop).replace(/%image-delete-url%/g, media.deleteUrl).replace(/%image-id%/g, media.id))
                    $('#dev-profilePhoto').closest('tr').replaceWith(temepelate);
                    $('[data-popup="popover"]').popover();


                    // Tooltip
                    $('[data-popup="tooltip"]').tooltip({
                        trigger: 'hover'
                    });

                } else {
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                            .replace(/%image-id%/g, '')
                            .replace(/%name%/g, 'profilePhoto')
                            .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, 'profilePhoto'))
                            .replace(/%arabicName%/g, imageErrorMessages.profilePhoto)
                            .replace(/%errorDimission%/g, imageErrorMessages.imageDimension)
                            .replace(/%cropButton%/g, '')
                            .replace(/%deleteButton%/g, '');
                    $('#dev-profilePhoto').replaceWith(temepelate);
                }
                $('[name="form[profileType]"]').each(function () {
                    if ($(this).val() == "image" && $(this).prop("checked")) {
                        $('#dev-profilePhoto').show();
                        $('#dev-profileVideo').hide();
                    } else if ($(this).val() == "video" && $(this).prop("checked")) {
                        $('#dev-profileVideo').show();
                        $('#dev-profilePhoto').hide();
                    }
                })
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

function tdLoadingToggle(elm) {
    var loaderDiv = '<div class=" btn dev-spinner-container" disabled=""><i class="spinner icon-spinner"></i></div>';
    if (!$(elm).is('td')) {
        elm = $(elm).parent('td');
    }
    if ($(elm).length > 0) {
        if ($(elm).find('.dev-spinner-container').length > 0) {
            $(elm).find('.dev-spinner-container').remove();
            $(elm).children().show();
        } else {
            $(elm).children().hide();
            $(elm).append(loaderDiv);
        }
    }
}
var uploadpopup = false;
$(document).ready(function () {


    $("form.form-horizontal").data("validator").settings.ignore = [];






    $(document).on('click', '.dev-upload-image', function () {
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
    $(document).on('click', '.dev-upload-image-natural', function () {
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
        $('#uploadImg-natural .cropit-image-input').click();
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
            $('#image-cropper-modal').cropit('previewSize', {width: 585, height: 300});
            $('#image-cropper-modal').cropit('exportZoom', 2);
            var elementDimessionWidth = 1170;
            var elementDimessionHeight = 600;
            var typeAllowed = ['jpeg', 'jpg', 'png', 'gif'];
            var errorDimension = imageErrorMessages.imageDimension;
            var errorExtension = imageErrorMessages.imageGifExtension;
            if ($(element).closest('td').attr('data-name') == 'bannerPhoto') {
                errorDimension = imageErrorMessages.BannerimageDimension;
            }
            if ($(element).closest('td').attr('data-name') == 'profilePhoto') {
                errorDimension = imageErrorMessages.profileImageDimension;
                errorExtension = imageErrorMessages.imageExtension;
                var elementDimessionWidth = 200;
                var elementDimessionHeight = 200;
                $('#image-cropper-modal').cropit('previewSize', {width: 300, height: 300});
                $('#image-cropper-modal').cropit('exportZoom', .7);
                typeAllowed = ['jpeg', 'jpg', 'png'];
            }
            var elementObject = $('#uploadImg .cropit-image-input');
            if (elementObject.val()) {

                var value = elementObject.val(),
                        file = value.toLowerCase(),
                        extension = file.substring(file.lastIndexOf('.') + 1);

                if ($.inArray(extension, typeAllowed) == -1) {
                    showNotificationMsg(errorExtension, "", 'error');
                    $('#uploadImg').modal('hide');
                    return;

                } else if (elementObject.attr('data-size') > (4 * 1024 * 1024)) {
                    $('#uploadImg').modal('hide');
                    showNotificationMsg(imageErrorMessages.sizeError, "", 'error');
                    return;


                } else if ($('#image-cropper-modal').cropit('imageSize').width < elementDimessionWidth || $('#image-cropper-modal').cropit('imageSize').height < elementDimessionHeight) {
                    $('#uploadImg').modal('hide');
                    showNotificationMsg(errorDimension, "", 'error');
                    return;


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



    $('#image-cropper-natural-modal').cropit({
        smallImage: 'stretch',
        minZoomstring: 'fill',
        imageBackground: true,
        imageBackgroundBorderWidth: 30,
        onImageLoaded: function () {
            if (type == 'upload') {
                $('.dev-submit-image-natural').attr('data-url', uploadUrl)
                $('.dev-submit-image-natural').attr('data-id', '')
            } else if (type == 'crop') {
                $('.dev-submit-image-natural').attr('data-url', element.attr('data-crop-url'))
                $('.dev-submit-image-natural').attr('data-id', element.attr('id'))

            }
            var elementDimessionWidth = 300;
            var elementDimessionHeight = 600;
           // alert('haaa')
            var errorDimension = imageErrorMessages.NaturalImageDimension;
            if ($(element).closest('td').attr('data-name') == 'bannerPhoto') {
                errorDimension = imageErrorMessages.BannerimageDimension;
            }
            var elementObject = $('#uploadImg-natural .cropit-image-input');
            if (elementObject.val()) {

                var value = elementObject.val(),
                        file = value.toLowerCase(),
                        extension = file.substring(file.lastIndexOf('.') + 1);

                if ($.inArray(extension, ['jpeg', 'jpg', 'png']) == -1) {
                    showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
                    $('#uploadImg-natural').modal('hide');
                    return;

                } else if (elementObject.attr('data-size') > (4 * 1024 * 1024)) {
                    $('#uploadImg-natural').modal('hide');
                    showNotificationMsg(imageErrorMessages.sizeError, "", 'error');
                    return;


                } else if ($('#image-cropper-natural-modal').cropit('imageSize').width < elementDimessionWidth || $('#image-cropper-natural-modal').cropit('imageSize').height < elementDimessionHeight) {
                    $('#uploadImg-natural').modal('hide');
                    showNotificationMsg(errorDimension, "", 'error');
                    return;


                } else {

                    $('#uploadImg-natural').modal('show');
                }
            }

        }, onImageError: function () {
            $('#uploadImg-natural').modal('hide');
            showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
        },
        onFileReaderError: function () {
            $('#uploadImg-natural').modal('hide');
            showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
        }

    })
    $('#image-cropper-natural-modal').cropit('previewSize', {width: 200, height: 400});
    $('#image-cropper-natural-modal').cropit('exportZoom', 1.5);



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
                } else if (data.status == 'success') {
                    var errorDimission = imageErrorMessages.imageDimension;
                    if (name == 'bannerPhoto') {
                        errorDimission = imageErrorMessages.BannerimageDimension;
                    }
                    var media = data.media;
                    if (name != 'profilePhoto' && name != 'bannerPhoto') {
                          addImageToSortView($('.filesUploaded table tbody'), media);
                   } else {

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
                    }
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
    $(document).on('click', '.dev-submit-image-natural', function () {
        var imageFile = $('#image-cropper-natural-modal').cropit('export')
        var formData = new FormData();
        formData.append("media[file]", imageFile);
        $('.dev-crop-spinner').show();
        $('.dev-submit-image-natural').hide();
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
                } else if (data.status == 'success') {
                    var errorDimission = imageErrorMessages.imageDimension;
                    if (name == 'bannerPhoto') {
                        errorDimission = imageErrorMessages.BannerimageDimension;
                    }
                    var media = data.media;


                    var temepelate = imageNaturalTempelate.replace(/%image-url%/g, '/' + media.imageUrl)
                            .replace(/%image-id%/g, media.id)
                            .replace(/%name%/g, name)
                            .replace(/%image-delete-url%/g, media.deleteUrl)
                            .replace(/%arabicName%/g, imageErrorMessages[name])
                            .replace(/%errorDimission%/g, errorDimission)
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropNaturalButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                            .replace(/%deleteButton%/g, deleteNaturalButton.replace(/%pop-block%/g, media.pop).replace(/%image-delete-url%/g, media.deleteUrl).replace(/%image-id%/g, media.id))
                    element.closest('tr').replaceWith(temepelate);
                    showNotificationMsg(data.message, "", data.status);
                    $('#uploadImg-natural').modal('hide');
                    $('[data-popup="popover"]').popover();


                    // Tooltip
                    $('[data-popup="tooltip"]').tooltip({
                        trigger: 'hover'
                    });

                } else {
                    $('#uploadImg-natural').modal('hide');
                    showNotificationMsg(imageErrorMessages.generalError, "", 'error');
                    refreshImages();
                }
                $('.dev-crop-spinner').hide();
                $('.dev-submit-image-natural').show();
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
                        } else if (data.type == 'success') {
                            if ((closestTr.attr('id')).replace(/dev-/g, '') == 'bannerPhoto') {
                                errorDimission = imageErrorMessages.BannerimageDimension;
                            }
                            if ((closestTr.attr('id')).replace(/dev-/g, '') == 'bannerPhoto' || (closestTr.attr('id')).replace(/dev-/g, '') == 'profilePhoto' || (closestTr.attr('id')).replace(/dev-/g, '') == 'naturalPhoto') {

                                if ((closestTr.attr('id')).replace(/dev-/g, '') == 'naturalPhoto') {
                                    var temepelate = imageNaturalTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                                            .replace(/%image-id%/g, '')
                                            .replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, ''))
                                            .replace(/%arabicName%/g, imageErrorMessages[(closestTr.attr('id')).replace(/dev-/g, '')])
                                            .replace(/%errorDimission%/g, errorDimission)
                                            .replace(/%uploadButton%/g, uploadNaturalButton.replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, '')))
                                            .replace(/%cropButton%/g, '')
                                            .replace(/%deleteButton%/g, '');
                                } else {
                                    var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                                            .replace(/%image-id%/g, '')
                                            .replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, ''))
                                            .replace(/%arabicName%/g, imageErrorMessages[(closestTr.attr('id')).replace(/dev-/g, '')])
                                            .replace(/%errorDimission%/g, errorDimission)
                                            .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, '')))
                                            .replace(/%cropButton%/g, '')
                                            .replace(/%deleteButton%/g, '');
                                }

                                closestTr.replaceWith(temepelate);
                            } else {
                                $this.closest('tr').remove()
                                closestTr.remove();
                                setUploadedImagesCount();
                                setUploadedVideosCount();
                            }
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
    $(document).on('click', '.dev-crop-images-natural', function () {
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
        $('#uploadImg-natural .cropit-image-input').click();
//        $('#uploadImg').modal('show');

    })
    // Handle rotation
    $(document).on('click', '.rotate-cw-btn', function () {
        $('#image-cropper-modal').cropit('rotateCW');
    });
    $(document).on('click', '.rotate-ccw-btn', function () {
        $('#image-cropper-modal').cropit('rotateCCW');
    });

    $('body').on('preAjaxCallback', function () {

//        updateMinRelated();
//        if($('#product_minimumRelatedRecipe').val()==''){
//            showNotificationMsg("يجب الا تقل الوصفات والنصائح والمقالات عن 2", "", "error");
//            return false;
//        }

    });
    $(document).on('openTab', function () {
        $("form.form-horizontal").data("validator").settings.ignore = [];
        if ($('.help-block:eq(0)').closest('.tab-pane').length > 0) {
            $('a[href="#' + $('.help-block:eq(0)').closest('.tab-pane').attr('id') + '"').click();
        }
    });

    $(document).on('onFailSubmitForm', function () {
        refreshImages();
        refreshMediaSortView();
    })

})