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
        stack:  {"dir1": "down", "dir2": "right", "firstpos1": 0, "firstpos2": 0}
    });
}


function searchRelatedMaterialResult(response) {
    var $this = $('#related-materials-source');


    if ($this.closest('form').attr('ajax-running') === 'true') {
        return;
    }

    $this.parents('.form-group').removeClass('has-error').find('.help-block').remove();
    $this.val("");

    $(response.data).each(function(){
        if (this.valid) {
            RelatedRecipeObj.push({
                'id':this.id,
                'title':this.title,
                'slug':this.slug
            });
        } else {
            showNotification(this.message, 'error');
        }

        $('#material_type_relatedMaterials').val(JSON.stringify(RelatedRecipeObj));
        updateRelatedMaterial();
    });

}


function searchRelatedMaterial(element) {
        var $this = $(element);
        if($this.val().trim() == "") return;

        if ($this.valid()) {
            quickAddRefreshFunctionParameter = $this.attr('data-refresh-function-parameter');
            setSearchIframeUrl($this);
            $('#iframeModal .modal-title').html($('.dev-search-related-material').attr('data-original-title'));
            $('#iframeModal').modal('show');
            $('html').css('overflow-x','visible');
        }
}

function setSearchIframeUrl($this) {
    ids = getExistingRelatedMaterial();
    $('#iFrameResizer0').contents().find('body').html('');
//    document.getElementById('iFrameResizer0').contentDocument.body.innerHTML="";
    $('#iframeModal iframe').attr('src', $this.attr('data-search-url') + '&searchString=' + $("#related-materials-source").val());
}

function updateRelatedMaterial(){
    if($('#recipe_related').length > 0){
        var data = JSON.parse($('#recipe_related').val());
        RelatedRecipeObj = data;
        $('.dev-related-list').html("");
        $(data).each(function(){
            $('.dev-related-list').append('<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="'+this.img +'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>');
//            $('.dev-related-list').append('<li class="media dev-related-item"><div class="media-body"><a href="'+$('base').attr('href')+this.slug+'" target="_blank">'+this.title+'</a>  </div><div class="dev-delete-related-material media-right" data-related-material-id="'+this.id+'" data-related-material-slug="'+this.slug+'"><i class="icon icon-cross2"></i></div></li>');
        });
    }
}

function updateRelatedArticle(){
    if($('#recipe_related_article').length > 0){
        var data = JSON.parse($('#recipe_related_article').val());
        RelatedArticleObj = data;
//        $('.dev-related-article-list').html("");
        var articles = '<label class="control-label col-lg-2 dev-related-article-list" for="recipe_relatedRecipe"></label><div class="col-lg-12" style="padding: 0;"><ul class="dev-related-article-list media-list width-350 notificationList">';
        $(data).each(function(){
            articles += '<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="'+this.img+'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-article-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>';
//            $('.dev-related-list').append('<li class="media dev-related-item"><div class="media-body"><a href="'+$('base').attr('href')+this.slug+'" target="_blank">'+this.title+'</a>  </div><div class="dev-delete-related-material media-right" data-related-material-id="'+this.id+'" data-related-material-slug="'+this.slug+'"><i class="icon icon-cross2"></i></div></li>');
        });
        articles += "</ul></div></div>"

        if($('#recipe_relatedArticle').parent().find('.dev-related-article-list').length > 0) {
            $('label.dev-related-article-list').remove();
            $('.dev-related-article-list').parent().remove();
        }
        $('#recipe_relatedArticle').parent().append(articles);
        updateMinRelated();
    }
}

function updateRelatedTip(){
    if($('#recipe_related_tip').length > 0){
        var data = JSON.parse($('#recipe_related_tip').val());
        RelatedTipObj = data;
//        $('.dev-related-tip-list').html("");

        var tips = '<label class="control-label col-lg-2 dev-related-tip-list" for="recipe_relatedRecipe"></label><div class="col-lg-12" style="padding: 0;"><ul class="dev-related-tip-list media-list width-350 notificationList">';
        $(data).each(function(){
            tips += '<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="'+this.img+'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-tip-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>';
//            $('.dev-related-tip-list').append('<li class="media" data-related-material-id="'+this.id+'"><div class="media-left"><img src="/'+(this.img).replace(/^\/+/g,'')+'" class="img-circle" alt=""></div><div class="media-body"><b> '+this.text+'</b></div><div class="media-right"><a class="dev-related-delete" href="#" data-related-material-id="'+this.id+'"><i class="icon icon-cross2"></i></a></div></li>');
//            $('.dev-related-list').append('<li class="media dev-related-item"><div class="media-body"><a href="'+$('base').attr('href')+this.slug+'" target="_blank">'+this.title+'</a>  </div><div class="dev-delete-related-material media-right" data-related-material-id="'+this.id+'" data-related-material-slug="'+this.slug+'"><i class="icon icon-cross2"></i></div></li>');
        });
        tips += "</ul></div></div>"
        if($('#recipe_relatedTip').parent().find('.dev-related-tip-list').length > 0) {
           $('label.dev-related-tip-list').remove();
           $('.dev-related-tip-list').parent().remove();
        }
        $('#recipe_relatedTip').parent().append(tips);
        updateMinRelated();
    }
}

function updateMinRelated() {
    if ($('.dev-related-article-list').length > 0 || $('.dev-related-tip-list').length > 0 ) {
        if (($('.dev-related-article-list li').length != 0 && $('.dev-related-article-list li').length < 2) || ($('.dev-related-tip-list li').length != 0 && $('.dev-related-tip-list li').length < 2))
        {
            $('#recipe_minvalue').val('')
        } else {
            $('#recipe_minvalue').val('valid')
        }
    } else {
        $('#recipe_minvalue').val('valid')
    }
}
function getExistingRelatedMaterial(){
    if(typeof RelatedMaterialObj == "undefined"){
        RelatedMaterialObj = new Array();
    }
    return $.map( RelatedMaterialObj, function( value, key ) {
                return value.id;
            });
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param array elementsIds
 */
function removeElementsByIds(elementsIds) {
    if (elementsIds) {
        for (var i = 0; i < elementsIds.length; i++) {
            $('#' + elementsIds[i]).remove();
        }
    }
}
function checkRelatedMaterials() {
        var $this = $('#related-materials-source');
        window.self.close();
        $this.removeAttr('data-force-published-valid');
        $this.removeAttr('data-published-valid');
        var $loader = $this.closest('.form-group').find('.InputLoader').show();

        if($this.parents('.form-group').find('#recipe_relatedRecipe').val() != ""){
            RelatedMaterialObj = JSON.parse($this.parents('.form-group').find('#recipe_relatedRecipe').val());
        }else{
            RelatedMaterialObj = [];
        }
        var existingIds = getExistingRelatedMaterial();

        $.ajax({
            url: $this.attr('bulk-data-url'),
            data: {existing: existingIds, new : ids, id: requestId},
            success: function (data) {
                ids = getExistingRelatedMaterial();
                searchRelatedMaterialResult(data);
                $('#iframeModal').modal('hide');
                $('html').css('overflow-x','visible');
            },
            error: function () {
            },
            complete: function () {
                $loader.hide();
            }
        });
}


/**
 *
 * get materials for releated search results
 *
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
function searchRelatedMaterialResult(response) {
    var $this = $('#related-materials-source');


    if ($this.closest('form').attr('ajax-running') === 'true') {
        return;
    }

    $this.parents('.form-group').removeClass('has-error').find('.help-block').remove();
    $this.val("");

    $(response.data).each(function(){
        if (this.valid) {
            RelatedMaterialObj.push({
                'id':this.id,
                'title':this.title,
                'slug':this.slug
            });
        } else {
            showNotification(this.message, 'error');
        }

        $('#recipe_relatedRecipe').val(JSON.stringify(RelatedMaterialObj));
        updateRelatedMaterial();
    });

}



function addRelatedMaterial(data) {
    RelatedRecipeObj.push({
        'id':data.id,
        'text':data.text,
        'img':data.img
    });
    $('#recipe_related').val(JSON.stringify(RelatedRecipeObj));
    updateRelatedMaterial();

//    checkPublishedValidation(element);
}

function addRelatedArticles(data) {
    RelatedArticleObj.unshift({
        'id':data.id,
        'text':data.text,
        'img':data.img
    });
    $('#recipe_related_article').val(JSON.stringify(RelatedArticleObj));
    updateRelatedArticle();

//    checkPublishedValidation(element);
}

function addRelatedTip(data) {
    RelatedTipObj.unshift({
        'id': data.id,
        'text': data.text,
        'img': data.img
    });
    $('#recipe_related_tip').val(JSON.stringify(RelatedTipObj));
    updateRelatedTip();
}

function checkPublishedValidation(element) {
    var $this = $(element);
    if($this.val().trim() == "") return;
    if ($this.valid()) {
        $this.removeAttr('data-force-published-valid');
        $this.removeAttr('data-published-valid');
        var $loader = $this.closest('.form-group').find('.InputLoader').show();
        if($this.parents('.form-group').find('#recipe_relatedRecipe').val() != ""){
            RelatedMaterialObj = JSON.parse($this.parents('.form-group').find('#recipe_relatedRecipe').val());
        }else{
            RelatedMaterialObj = [];
        }
        var ids     = $.map( RelatedMaterialObj, function( value, key ) {
            return value.id;
        });

        $.ajax({
            url: $this.attr('data-url'),
            data: {existing: ids, fieldValue: $this.val(), id: requestId},
            success: function (data) {
                if ($this.closest('form').attr('ajax-running') === 'true') {
                    return;
                }

                $this.parents('.form-group').removeClass('has-error').find('.help-block').remove();

                if (data.valid) {
                    $this.val("");
                    RelatedMaterialObj.push({
                        'id':data.id,
                        'title':data.title,
                        'slug':data.slug
                    });
                    $('#recipe_relatedRecipe').val(JSON.stringify(RelatedMaterialObj));
                    updateRelatedMaterial();
                }else{
                    $this.attr('data-validation-message', data.message);
                    markElementAsNotValid($this);
                }
            },
            error: function () {
            },
            complete: function () {
                $loader.hide();
            }
        });
    }
}

$('#recipe_relatedRecipe').on('select2:select',function(e){
    addRelatedMaterial(e.params.data);
    $(this).val(null).trigger("change");
});
$('#recipe_relatedArticle').on('select2:select',function(e){
    addRelatedArticles(e.params.data);
    $(this).val(null).trigger("change");
});
$('#recipe_relatedTip').on('select2:select',function(e){
    addRelatedTip(e.params.data);
    $(this).val(null).trigger("change");
});

$(document).on("click",'.dev-add-related-material',function(){
        addRelatedMaterial($('#related-materials-source'));
    });

    $(document).on("click",'.dev-search-related-material',function(){
        searchRelatedMaterial($('#related-materials-source'));
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
                                    'img':"/"+$(this).find('img').attr('src')
                                });
                            });
                            $('#recipe_related').val(JSON.stringify(objArray));
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
                $('#recipe_related').val(JSON.stringify(objArray));
                RelatedRecipeObj = objArray;
            }
    });

    $(document).on('click', '.dev-related-article-delete', function(e) {
        e.preventDefault();
            var $this = $(this);

//            if(document.location.pathname.indexOf('edit') >= 0){
//                if ($('.dev-related-article-list li').length <= 2) {
//                    showNotificationMsg(" لا يمكن الحذف حيث ان الحد الادنى للمقالات ذات صله 2", "", "error");
//                    return false;
//                }
//                $.ajax({
//                    url: relatedMaterialDeleteUrl,
//                    method: 'POST',
//                    data:{parent:requestId,child:$this.attr('data-related-material-id')},
//                    success: function(data) {
//                        if(data.status == "success"){
//                            $this.parents('li').remove();
//                            var objArray = [];
//                            $.each($('.dev-related-list .media'),function(){
//                                objArray.push({
//                                    'id':$(this).attr('data-related-material-id'),
//                                    'text':$(this).find('.media-body b').text().trim(),
//                                    'img':"/"+$(this).find('img').attr('src')
//                                });
//                            });
//                            $('#recipe_related').val(JSON.stringify(objArray));
//                            updateRelatedMaterial();
//                        }
//
//                        showNotificationMsg(data.message,"");
//                        updateMinRelated();
//
//                    }
//                });
//            }else{
                $this.parents('li').remove();
                var objArray = [];
                var objectElement;
                $.each($('.dev-related-article-list .media'),function(){
                  objectElement=$(this)
                    objArray.push({
                        'id':objectElement.attr('data-related-material-id'),
                        'text':objectElement.find('.media-body b').text().trim(),
                        'img':objectElement.find('img').attr('src')
                    });
                });
                $('#recipe_related_article').val(JSON.stringify(objArray));
                RelatedArticleObj = objArray;
                updateMinRelated();
//            }
    });

    $(document).on('click', '.dev-related-tip-delete', function(e) {
        e.preventDefault();
            var $this = $(this);

//            if(document.location.pathname.indexOf('edit') >= 0){
//                if ($('.dev-related-tip-list li').length <= 2) {
//                    showNotificationMsg(" لا يمكن الحذف حيث ان الحد الادنى للنصائح ذات صله 2", "", "error");
//                    return false;
//                }
//                $.ajax({
//                    url: relatedMaterialDeleteUrl,
//                    method: 'POST',
//                    data:{parent:requestId,child:$this.attr('data-related-material-id')},
//                    success: function(data) {
//                        if(data.status == "success"){
//                            $this.parents('li').remove();
//                            var objArray = [];
//                            $.each($('.dev-related-list .media'),function(){
//                                objArray.push({
//                                    'id':$(this).attr('data-related-material-id'),
//                                    'text':$(this).find('.media-body b').text().trim(),
//                                    'img':"/"+$(this).find('img').attr('src')
//                                });
//                            });
//                            $('#recipe_related').val(JSON.stringify(objArray));
//                            updateRelatedMaterial();
//                        }
//
//                        showNotificationMsg(data.message,"");
//                        updateMinRelated();
//
//                    }
//                });
//            }else{
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
                $('#recipe_related_tip').val(JSON.stringify(objArray));
                RelatedTipObj = objArray;
                updateMinRelated();
//            }
    });






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
                            .replace(/%changeDefaultUrl%/g, media.changeCoverUrl)
                            .replace(/%arabicName%/g, imageErrorMessages.coverPhoto)
                            .replace(/%image-delete-url%/g, media.deleteUrl)
                            .replace(/%uploadButton%/g, '')
                            .replace(/%cropButton%/g, cropButton.replace(/%image-id%/g, media.id).replace(/%crop-url%/g, media.cropUrl))
                            .replace(/%deleteButton%/g, deleteButton.replace(/%pop-block%/g, media.pop).replace(/%image-delete-url%/g, media.deleteUrl).replace(/%image-id%/g, media.id))
                    $('#dev-coverPhoto').closest('tr').replaceWith(temepelate);
                    $('[data-popup="popover"]').popover({
                        delay:{ "hide": 500 }
                    });


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
                    $('[data-popup="popover"]').popover({
                        delay:{ "hide": 500 }
                    });


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

    updateMinRelated();
    $("form.form-horizontal").data("validator").settings.ignore = [];

//    if($('#recipe_country').val() == ""){
//        $('#recipe_country').val($('#recipe_country option:eq(1)').val()).trigger('change');
//    }

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

            }else{
                $('.dev-submit-image').attr('data-url', uploadUrl)
                $('.dev-submit-image').attr('data-id', '')
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

                } else if ($('#image-cropper-modal').cropit('imageSize').width < 1000 || $('#image-cropper-modal').cropit('imageSize').height < 700) {

                    showNotificationMsg(imageErrorMessages.imageDimension, "", 'error');

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

    });

    $('#image-cropper-modal').cropit('previewSize', {width: 500, height: 350});
    $('#image-cropper-modal').cropit('exportZoom', 2);


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
                }
                else if (data.status == 'success') {
                    var media = data.media;
                    addImageToSortView(media);
                    showNotificationMsg(data.message, "", data.status);
                    $('#uploadImg').modal('hide');
                    $('#GoogleImportImg-modal').modal('hide')
                    $('[data-popup="popover"]').popover({
                        delay: {"hide": 500}
                    });


                    // Tooltip
                    $('[data-popup="tooltip"]').tooltip({
                        trigger: 'hover'
                    });

                } else {
                    $('#uploadImg').modal('hide');
                    $('#GoogleImportImg-modal').modal('hide')
                    showNotificationMsg(imageErrorMessages.generalError, "", 'error');
                    refreshImages();
                }
                $(".styled, .multiselect-container input").uniform({
                    radioClass: 'choice'
                });
                $('.dev-crop-spinner').hide();
                $('.dev-submit-image').show();
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
    });

    $(document).on('click','.dev-cover-img-edit',function () {
        $.ajax({
            url: $(this).attr('data-change-default-url') ,
            success: function (data) {
                if (data.status == 'login') {
                    window.location = loginUrl + '?redirectUrl=' + encodeURIComponent(window.location.href);
                } else if (data.status == 'success') {
//                    showNotificationMsg(data.message,"");
                } else {
                    showNotificationMsg(imageErrorMessages.generalError, "", 'error');
                }
//                refreshImages();
            }
        });

    });

})