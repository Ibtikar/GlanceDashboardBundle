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

function updateMinRelated() {
    if ($('.dev-related-list').length > 0 || $('.dev-related-tip-list').length > 0 || $('.dev-related-article-list').length > 0) {
        if (($('.dev-related-list li').length != 0 && $('.dev-related-list li').length < 2) || ($('.dev-related-tip-list li').length != 0 && $('.dev-related-tip-list li').length < 2) || ($('.dev-related-article-list li').length != 0 && $('.dev-related-article-list li').length < 2))
        {
            $('#product_minimumRelatedRecipe').val('valid')
        } else {
            $('#product_minimumRelatedRecipe').val('valid')
        }
    } else {
        $('#product_minimumRelatedRecipe').val('valid')
    }
}

function updateRelatedArticle() {
    if ($('#product_related_article').length > 0) {
        var data = JSON.parse($('#product_related_article').val());
        RelatedArticleObj = data;
//        $('.dev-related-article-list').html("");
        var articles = '<label class="control-label col-lg-2 dev-related-article-list" for="product_relatedArticle"></label><div class="col-lg-12" style="padding: 0;"><ul class="dev-related-article-list media-list width-350 notificationList">';
        $(data).each(function () {
            articles += '<li class="media" data-related-material-id="' + this.id + '"><div class="media-left"><img src="' + this.img + '" class="img-circle" alt=""></div><div class="media-body"><b> ' + this.text + '</b></div><div class="media-right"><a class="dev-related-article-delete" href="#" data-related-material-id="' + this.id + '"><i class="icon icon-cross2"></i></a></div></li>';
//            $('.dev-related-list').append('<li class="media dev-related-item"><div class="media-body"><a href="'+$('base').attr('href')+this.slug+'" target="_blank">'+this.title+'</a>  </div><div class="dev-delete-related-material media-right" data-related-material-id="'+this.id+'" data-related-material-slug="'+this.slug+'"><i class="icon icon-cross2"></i></div></li>');
        });
        articles += "</ul></div></div>"

        if ($('#product_relatedArticle').parent().find('.dev-related-article-list').length > 0) {
            $('label.dev-related-article-list').remove();
            $('.dev-related-article-list').parent().remove();
        }
        $('#product_relatedArticle').parent().append(articles);
//        updateMinRelated();

    }
}

function updateRelatedTip() {
    if ($('#product_related_tip').length > 0) {
        var data = JSON.parse($('#product_related_tip').val());
        RelatedTipObj = data;
//        $('.dev-related-tip-list').html("");

        var tips = '<label class="control-label col-lg-2 dev-related-tip-list" for="product_relatedTip"></label><div class="col-lg-12" style="padding: 0;"><ul class="dev-related-tip-list media-list width-350 notificationList">';
        $(data).each(function () {
            tips += '<li class="media" data-related-material-id="' + this.id + '"><div class="media-left"><img src="' + this.img + '" class="img-circle" alt=""></div><div class="media-body"><b> ' + this.text + '</b></div><div class="media-right"><a class="dev-related-tip-delete" href="#" data-related-material-id="' + this.id + '"><i class="icon icon-cross2"></i></a></div></li>';
//            $('.dev-related-tip-list').append('<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="/'+(this.img).replace(/^\/+/g,'')+'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>');
//            $('.dev-related-list').append('<li class="media dev-related-item"><div class="media-body"><a href="'+$('base').attr('href')+this.slug+'" target="_blank">'+this.title+'</a>  </div><div class="dev-delete-related-material media-right" data-related-material-id="'+this.id+'" data-related-material-slug="'+this.slug+'"><i class="icon icon-cross2"></i></div></li>');
        });
        tips += "</ul></div></div>"
        if ($('#product_relatedTip').parent().find('.dev-related-tip-list').length > 0) {
            $('label.dev-related-tip-list').remove();
            $('.dev-related-tip-list').parent().remove();
        }
        $('#product_relatedTip').parent().append(tips);
//        updateMinRelated();

    }
}

function addRelatedArticles(data) {
    RelatedArticleObj.unshift({
        'id': data.id,
        'text': data.text,
        'img': data.img
    });
    $('#product_related_article').val(JSON.stringify(RelatedArticleObj));
    updateRelatedArticle();
    if (document.location.pathname.indexOf('edit') >= 0) {

        $.ajax({
            url: relatedMaterialAddUrl,
            method: 'POST',
            data: {parent: requestId, child: data.id},
            success: function (data) {
                showNotificationMsg(data.message, "");


            }
        });
    }

//    checkPublishedValidation(element);
}

function addRelatedTip(data) {
    RelatedTipObj.unshift({
        'id': data.id,
        'text': data.text,
        'img': data.img
    });
    $('#product_related_tip').val(JSON.stringify(RelatedTipObj));
    updateRelatedTip();
    if (document.location.pathname.indexOf('edit') >= 0) {

        $.ajax({
            url: relatedMaterialAddUrl,
            method: 'POST',
            data: {parent: requestId, child: data.id},
            success: function (data) {
                showNotificationMsg(data.message, "");


            }
        });
    }
}

function refreshImages() {
    $.ajax({
        url: refreshImagesUrl,
        success: function (data) {
            if (data.status == 'login') {
                window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
            } else {

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
                $('[name="form[profileType]"]').each(function(){
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

function uploadImageToServer(imageFile, url) {
        var formData = new FormData();
        formData.append("media[file]", imageFile);
        formData.append("fileName", fileName);
        $('.dev-crop-spinner').show();
        $('.dev-submit-image').hide();
        $.ajax({
            url: url ,
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
//    $('#product_minimumRelatedRecipe').val($('.dev-related-list li').length< 4? '':$('.dev-related-list li').length )
    updateMinRelated();


    $("form.form-horizontal").data("validator").settings.ignore = [];

    $('#product_relatedRecipe').on('select2:select', function (e) {
        addRelatedMaterial(e.params.data);
        $(this).val(null).trigger("change");
    });
    $('#product_relatedArticle').on('select2:select', function (e) {
        addRelatedArticles(e.params.data);
        $(this).val(null).trigger("change");
    });
    $('#product_relatedTip').on('select2:select', function (e) {
        addRelatedTip(e.params.data);
        $(this).val(null).trigger("change");
    });


    $(document).on('click', '.dev-related-article-delete', function (e) {
        e.preventDefault();
        var $this = $(this);

        if (document.location.pathname.indexOf('edit') >= 0) {
            $.ajax({
                url: relatedMaterialDeleteUrl,
                method: 'POST',
                data: {parent: requestId, child: $this.attr('data-related-material-id')},
                success: function (data) {
                    if (data.status == "success") {
                        $this.parents('li').remove();
                        var objArray = [];
                        $.each($('.dev-related-article-list .media'), function () {
                            objArray.push({
                                'id': $(this).attr('data-related-material-id'),
                                'text': $(this).find('.media-body b').text().trim(),
                                'img': $(this).find('img').attr('src')
                            });
                        });
                        $('#product_related_article').val(JSON.stringify(objArray));
                        updateRelatedArticle();
                    }

                    showNotificationMsg(data.message, "");

                }
            });
        } else {
            $this.parents('li').remove();
            var objArray = [];
            var objectElement;
            $.each($('.dev-related-article-list .media'), function () {
                objectElement = $(this)
                objArray.push({
                    'id': objectElement.attr('data-related-material-id'),
                    'text': objectElement.find('.media-body b').text().trim(),
                    'img': objectElement.find('img').attr('src')
                });
            });
            $('#product_related_article').val(JSON.stringify(objArray));
            RelatedArticleObj = objArray;
            updateMinRelated();

        }
    });

    $(document).on('click', '.dev-related-tip-delete', function (e) {
        e.preventDefault();
        var $this = $(this);

        if (document.location.pathname.indexOf('edit') >= 0) {
            $.ajax({
                url: relatedMaterialDeleteUrl,
                method: 'POST',
                data: {parent: requestId, child: $this.attr('data-related-material-id')},
                success: function (data) {
                    if (data.status == "success") {
                        $this.parents('li').remove();
                        var objArray = [];
                        $.each($('.dev-related-tip-list .media'), function () {
                            objArray.push({
                                'id': $(this).attr('data-related-material-id'),
                                'text': $(this).find('.media-body b').text().trim(),
                                'img': $(this).find('img').attr('src')
                            });
                        });
                        $('#product_related_tip').val(JSON.stringify(objArray));
                        updateRelatedTip();
                    }

                    showNotificationMsg(data.message, "");

                }
            });
        } else {
            $this.parents('li').remove();
            var objArray = [];
            var objectElement;
            $.each($('.dev-related-tip-list .media'), function () {
                objectElement = $(this)
                objArray.push({
                    'id': objectElement.attr('data-related-material-id'),
                    'text': objectElement.find('.media-body b').text().trim(),
                    'img': objectElement.find('img').attr('src')
                });
            });
            $('#product_related_tip').val(JSON.stringify(objArray));
            RelatedTipObj = objArray;
//            updateMinRelated();

        }
    });


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
            var typeAllowed = ['jpeg', 'jpg', 'png', 'gif'];
            var errorExtension = imageErrorMessages.imageGifExtension;

            if ($(element).closest('td').attr('data-name') == 'profilePhoto' || $(element).closest('td').attr('data-name') == 'bannerPhoto') {
                errorExtension = imageErrorMessages.imageExtension;

                typeAllowed = ['jpeg', 'jpg', 'png'];
            }
            $('#image-cropper-modal').cropit('previewSize', {width: 585, height: 300});
            $('#image-cropper-modal').cropit('exportZoom', 2);


            var errorDimension = imageErrorMessages.imageDimension;
            if ($(element).closest('td').attr('data-name') == 'bannerPhoto') {
                errorDimension = imageErrorMessages.BannerimageDimension;
                $('#image-cropper-modal').cropit('previewSize', {width: 1170, height: 200});
                $('#image-cropper-modal').cropit('exportZoom', 1);


            }
            var elementObject = $('#uploadImg .cropit-image-input');
            if (elementObject.val()) {

                var value = elementObject.val(),
                        file = value.toLowerCase(),
                        extension = file.substring(file.lastIndexOf('.') + 1);
                fileName = file;

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
                    return ;


                } else {

                    if (extension == 'gif') {
                        uploadImageToServer($('.cropit-preview-image').attr('src'), uploadUrl);
                    } else {
                        $('#uploadImg').modal('show');
                    }
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
                        if (data.media.coverPhoto) {
                            addImageToSortView($('.filesUploaded table tbody'), media);

                        } else {
                            addImageToSortViewActivity($('.filesActivityUploaded table tbody'), media);

                        }


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
                            if ((closestTr.attr('id')).replace(/dev-/g, '') == 'bannerPhoto' || (closestTr.attr('id')).replace(/dev-/g, '') == 'profilePhoto') {

                                var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                                        .replace(/%image-id%/g, '')
                                        .replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, ''))
                                        .replace(/%arabicName%/g, imageErrorMessages[(closestTr.attr('id')).replace(/dev-/g, '')])
                                        .replace(/%errorDimission%/g, errorDimission)
                                        .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g, '')))
                                        .replace(/%cropButton%/g, '')
                                        .replace(/%deleteButton%/g, '');
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
    })

})