var RelatedRecipeObj = [];
var RelatedKitchen911Obj = [];
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
        stack:  {"dir1": "down", "dir2": "right", "firstpos1": 0, "firstpos2": 0}
    });
}


function addRelatedMaterial(data) {
    RelatedRecipeObj.push({
        'id': data.id,
        'text': data.text,
        'img': data.img
    });
    $('#form_related').val(JSON.stringify(RelatedRecipeObj));
    updateRelatedMaterial();
    if (document.location.pathname.indexOf('edit') >= 0) {

        $.ajax({
            url: relatedMaterialAddUrl,
            method: 'POST',
            data: {parent: requestId, child: data.id},
            success: function (data) {


            }
        });
    }

//    checkPublishedValidation(element);
}


function updateRelatedMaterial() {
    if ($('#form_related').length > 0) {
        var data = JSON.parse($('#form_related').val());
        RelatedRecipeObj = data;
        var recipes = '<label class="control-label col-lg-2 dev-related-recipe-list" for="form_relatedRecipe"></label><div class="col-lg-12" style="padding: 0;"><ul class="dev-related-list media-list width-350 notificationList">';
        $(data).each(function () {
            recipes += '<li class="media" data-related-material-id="' + this.id + '"><div class="media-left"><img src="' + this.img+ '" class="img-circle" alt=""></div><div class="media-body"><b> ' + this.text + '</b></div><div class="media-right"><a class="dev-related-delete" href="#" data-related-material-id="' + this.id + '"><i class="icon icon-cross2"></i></a></div></li>';
        });
        recipes += "</ul></div></div>"
        $('.dev-related-list').html("");

        if ($('#form_relatedRecipe').parent().find('.dev-related-recipe-list').length > 0) {
            $('label.dev-related-recipe-list').remove();
            $('.dev-related-recipe-list').parent().remove();
        }
        $('#form_relatedRecipe').parent().append(recipes);
        $('#form_minimumRelatedRecipe').val($('.dev-related-list li').length< 4? '':$('.dev-related-list li').length )

    }

}

function updateRelatedKitchen911(){
    if($('#form_related_kitchen911').length > 0){
        var data = JSON.parse($('#form_related_kitchen911').val());
        RelatedKitchen911Obj = data;
//        $('.dev-related-kitchen911-list').html("");
        var kitchen911s = '<label class="control-label col-lg-2 dev-related-kitchen911-list" for="form_relatedKitchen911"></label><div class="col-lg-12" style="padding: 0;"><ul class="dev-related-kitchen911-list media-list width-350 notificationList">';
        $(data).each(function(){
            kitchen911s += '<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="'+this.img+'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-kitchen911-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>';
//            $('.dev-related-list').append('<li class="media dev-related-item"><div class="media-body"><a href="'+$('base').attr('href')+this.slug+'" target="_blank">'+this.title+'</a>  </div><div class="dev-delete-related-material media-right" data-related-material-id="'+this.id+'" data-related-material-slug="'+this.slug+'"><i class="icon icon-cross2"></i></div></li>');
        });
        kitchen911s += "</ul></div></div>"

        if($('#form_relatedKitchen911').parent().find('.dev-related-kitchen911-list').length > 0) {
            $('label.dev-related-kitchen911-list').remove();
            $('.dev-related-kitchen911-list').parent().remove();
        }
        $('#form_relatedKitchen911').parent().append(kitchen911s);
    }
}

function updateRelatedTip(){
    if($('#form_related_tip').length > 0){
        var data = JSON.parse($('#form_related_tip').val());
        RelatedTipObj = data;
//        $('.dev-related-tip-list').html("");

        var tips = '<label class="control-label col-lg-2 dev-related-tip-list" for="form_relatedTip"></label><div class="col-lg-12" style="padding: 0;"><ul class="dev-related-tip-list media-list width-350 notificationList">';
        $(data).each(function(){
            tips += '<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="'+this.img+'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-tip-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>';
//            $('.dev-related-tip-list').append('<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="/'+(this.img).replace(/^\/+/g,'')+'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>');
//            $('.dev-related-list').append('<li class="media dev-related-item"><div class="media-body"><a href="'+$('base').attr('href')+this.slug+'" target="_blank">'+this.title+'</a>  </div><div class="dev-delete-related-material media-right" data-related-material-id="'+this.id+'" data-related-material-slug="'+this.slug+'"><i class="icon icon-cross2"></i></div></li>');
        });
        tips += "</ul></div></div>"
        if($('#form_relatedTip').parent().find('.dev-related-tip-list').length > 0) {
           $('label.dev-related-tip-list').remove();
           $('.dev-related-tip-list').parent().remove();
        }
        $('#form_relatedTip').parent().append(tips);
    }
}

function addRelatedKitchen911s(data) {
    RelatedKitchen911Obj.unshift({
        'id':data.id,
        'text':data.text,
        'img':data.img
    });
    $('#form_related_kitchen911').val(JSON.stringify(RelatedKitchen911Obj));
    updateRelatedKitchen911();

//    checkPublishedValidation(element);
}

function addRelatedTip(data) {
    RelatedTipObj.unshift({
        'id':data.id,
        'text':data.text,
        'img':data.img
    });
    $('#form_related_tip').val(JSON.stringify(RelatedTipObj));
    updateRelatedTip();
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
                            .replace(/%image-delete-url%/g, media.deleteUrl)
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                            .replace(/%deleteButton%/g, deleteButton.replace(/%pop-block%/g, media.pop).replace(/%image-delete-url%/g, media.deleteUrl).replace(/%image-id%/g, media.id))
                    $('#dev-coverPhoto').closest('tr').replaceWith(temepelate);
                    $('[data-popup="popover"]').popover();


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
                                    .replace(/%cropButton%/g, '')
                                    .replace(/%deleteButton%/g, '');
                             $('#dev-coverPhoto').replaceWith(temepelate);
                }
                if (data.profilePhoto) {
                    var media = data.profilePhoto;
                    var temepelate = imageTempelate.replace(/%image-url%/g, '/' + media.imageUrl)
                            .replace(/%image-id%/g, media.id)
                            .replace(/%name%/g, 'profilePhoto')
                            .replace(/%arabicName%/g, imageErrorMessages.profilePhoto)

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

                }else{
                     var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                                    .replace(/%image-id%/g, '')
                                    .replace(/%name%/g, 'profilePhoto')
                                    .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, 'profilePhoto'))
                                    .replace(/%arabicName%/g, imageErrorMessages.profilePhoto)
                                    .replace(/%cropButton%/g, '')
                                    .replace(/%deleteButton%/g, '');
                             $('#dev-profilePhoto').replaceWith(temepelate);
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
    $('#form_minimumRelatedRecipe').val($('.dev-related-list li').length< 4? '':$('.dev-related-list li').length )

    $("form.form-horizontal").data("validator").settings.ignore = [];

$('#form_relatedRecipe').on('select2:select',function(e){
    addRelatedMaterial(e.params.data);
    $(this).val(null).trigger("change");
});
$('#form_relatedKitchen911').on('select2:select',function(e){
    addRelatedKitchen911s(e.params.data);
    $(this).val(null).trigger("change");
});
$('#form_relatedTip').on('select2:select',function(e){
    addRelatedTip(e.params.data);
    $(this).val(null).trigger("change");
});

$(document).on("click",'.dev-add-related-material',function(){
        addRelatedMaterial($('#related-materials-source'));
    });

   $(document).on("keyup",'#related-materials-source',function(e){
        if(e.keyCode == 13){
            addRelatedMaterial($(this));
        }else{
            $(this).parents('.form-group').removeClass('has-error').find('.help-block').remove();
            $(this).removeAttr('data-validation-message');
        }
    });

    $(document).on('click', '.dev-related-delete', function(e) {
        e.preventDefault();
            var $this = $(this);

            if(document.location.pathname.indexOf('edit') >= 0){
                if ($('.dev-related-list li').length <= 4) {
                    showNotificationMsg(" لا يمكن الحذف حيث ان الحد الادنى للوصفات ذات صله 4", "", "error");
                    return false;
                }
                $.ajax({
                    url: relatedMaterialDeleteUrl,
                    method: 'POST',
                    data:{parent:requestId,child:$this.attr('data-related-material-id')},
                    success: function(data) {
                        if(data.status == "success"){
                            $this.parents('li').remove();
                            var objArray = [];
                            $.each($('.dev-related-list .media'),function(){
                                objArray.push({
                                    'id':$(this).attr('data-related-material-id'),
                                    'text':$(this).find('.media-body b').text().trim(),
                                    'img':$(this).find('img').attr('src')
                                });
                            });
                            $('#form_related').val(JSON.stringify(objArray));
                            updateRelatedMaterial();
                        }

                        showNotificationMsg(data.message,"");

                    }
                });
            }else{
                $this.parents('li').remove();
                var objArray = [];
                var objectElement;
                $.each($('.dev-related-list .media'),function(){
                  objectElement=$(this)
                    objArray.push({
                        'id':objectElement.attr('data-related-material-id'),
                        'text':objectElement.find('.media-body b').text().trim(),
                        'img':objectElement.find('img').attr('src')
                    });
                });
                $('#form_related').val(JSON.stringify(objArray));
                RelatedRecipeObj = objArray;
                            updateRelatedMaterial();

        $('#form_minimumRelatedRecipe').val($('.dev-related-list li').length< 4? '':$('.dev-related-list li').length )

            }

    });

    $(document).on('click', '.dev-related-kitchen911-delete', function(e) {
        e.preventDefault();
            var $this = $(this);

            if(document.location.pathname.indexOf('edit') >= 0){
                $.ajax({
                    url: relatedMaterialDeleteUrl,
                    method: 'POST',
                    data:{parent:requestId,child:$this.attr('data-related-material-id')},
                    success: function(data) {
                        if(data.status == "success"){
                            $this.parents('li').remove();
                            var objArray = [];
                            $.each($('.dev-related-list .media'),function(){
                                objArray.push({
                                    'id':$(this).attr('data-related-material-id'),
                                    'text':$(this).find('.media-body b').text().trim(),
                                    'img':$(this).find('img').attr('src')
                                });
                            });
                            $('#form_related').val(JSON.stringify(objArray));
                            updateRelatedMaterial();
                        }

                        showNotificationMsg(data.message,"");

                    }
                });
            }else{
                $this.parents('li').remove();
                var objArray = [];
                var objectElement;
                $.each($('.dev-related-kitchen911-list .media'),function(){
                  objectElement=$(this)
                    objArray.push({
                        'id':objectElement.attr('data-related-material-id'),
                        'text':objectElement.find('.media-body b').text().trim(),
                        'img':objectElement.find('img').attr('src')
                    });
                });
                $('#form_related_kitchen911').val(JSON.stringify(objArray));
                RelatedKitchen911Obj = objArray;
            }
    });

    $(document).on('click', '.dev-related-tip-delete', function(e) {
        e.preventDefault();
            var $this = $(this);

            if(document.location.pathname.indexOf('edit') >= 0){
                $.ajax({
                    url: relatedMaterialDeleteUrl,
                    method: 'POST',
                    data:{parent:requestId,child:$this.attr('data-related-material-id')},
                    success: function(data) {
                        if(data.status == "success"){
                            $this.parents('li').remove();
                            var objArray = [];
                            $.each($('.dev-related-list .media'),function(){
                                objArray.push({
                                    'id':$(this).attr('data-related-material-id'),
                                    'text':$(this).find('.media-body b').text().trim(),
                                    'img':$(this).find('img').attr('src')
                                });
                            });
                            $('#form_related').val(JSON.stringify(objArray));
                            updateRelatedMaterial();
                        }

                        showNotificationMsg(data.message,"");

                    }
                });
            }else{
                $this.parents('li').remove();
                var objArray = [];
                var objectElement;
                $.each($('.dev-related-tip-list .media'),function(){
                  objectElement=$(this)
                    objArray.push({
                        'id':objectElement.attr('data-related-material-id'),
                        'text':objectElement.find('.media-body b').text().trim(),
                        'img':objectElement.find('img').attr('src')
                    });
                });
                $('#form_related_tip').val(JSON.stringify(objArray));
                RelatedTipObj = objArray;
            }
    });


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
        smallImage: 'allow',
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
            var elementObject = $('#uploadImg .cropit-image-input');
            if (elementObject.val()) {

                var value = elementObject.val(),
                        file = value.toLowerCase(),
                        extension = file.substring(file.lastIndexOf('.') + 1);

                if ($.inArray(extension, ['jpeg', 'jpg', 'png']) == -1) {
                    showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');

                } else if (elementObject.attr('data-size') > (4 * 1024 * 1024)) {

                    showNotificationMsg(imageErrorMessages.sizeError, "", 'error');

                } else if ($('#image-cropper-modal').cropit('imageSize').width < 200 || $('#image-cropper-modal').cropit('imageSize').height < 200) {

                    showNotificationMsg(imageErrorMessages.imageDimension, "", 'error');

                } else {

                    $('#uploadImg').modal('show');
                }
            }

        }, onImageError: function () {
            $('#uploadImg').modal('hide');
            showNotificationMsg(imageErrorMessages.imageExtension, "", 'error');
        },
        onFileReaderError: function(){
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
                if(data.status=='login'){
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
                            .replace(/%deleteButton%/g, deleteButton.replace(/%pop-block%/g, media.pop).replace(/%image-delete-url%/g, media.deleteUrl).replace(/%image-id%/g, media.id))
                    element.closest('tr').replaceWith(temepelate);
                    showNotificationMsg(data.message, "", data.status);
                    $('#uploadImg').modal('hide');
                    $('[data-popup="popover"]').popover();


                    // Tooltip
                    $('[data-popup="tooltip"]').tooltip({
                        trigger: 'hover'
                    });

                }else{
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
                        if(data.status=='login'){
                            window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
                        }
                        else if (data.type == 'success') {
                            var temepelate = imageTempelate.replace(/%image-url%/g, '/bundles/ibtikarshareeconomydashboarddesign/images/placeholder.jpg')
                                    .replace(/%image-id%/g, '')
                                    .replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g,''))
                                    .replace(/%arabicName%/g, imageErrorMessages[(closestTr.attr('id')).replace(/dev-/g,'')])
                                    .replace(/%uploadButton%/g, uploadButton.replace(/%name%/g, (closestTr.attr('id')).replace(/dev-/g,'')))
                                    .replace(/%cropButton%/g, '')
                                    .replace(/%deleteButton%/g, '');
                            closestTr.replaceWith(temepelate);
                            showNotificationMsg(data.message, "", data.status);

                        }else{
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

    $('body').on('preAjaxCallback', function () {
        if ($('#form_minimumRelatedRecipe').val() == '') {
            if ($('.dev-related-list li').length != 0) {
                showNotificationMsg("يجب اختيار على الاقل  4 وصفات ذات صله", "", "error");
                return false;
            } else {
                $('#form_minimumRelatedRecipe').val('valid');
            }
        }
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