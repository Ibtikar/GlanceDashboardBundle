var mediaContainer = [
//        {
//            id   :"",
//            order:"",
//            path:"",
//            caption:"",
//            type:""
//        }
];

var selectedCover = null;
var retryLimit = 3;
var retries = 0;

function populateData() {
    mediaContainer=[];
    $('#media-list-target-right').find('tr').each(function(index) {
        var imgSrc = $(this).find('img').attr('src');
        mediaContainer.push({
            id: $(this).attr('id'),
            vid: $(this).attr('video-id'),
            order: index,
            captionAr: typeof $(this).find('.dev-caption-ar').val() !== "undefined" ? $(this).find('.dev-caption-ar').val() : "",
            captionEn: typeof $(this).find('.dev-caption-en').val() !== "undefined" ? $(this).find('.dev-caption-en').val() : "",
            path: imgSrc.substr(imgSrc.lastIndexOf("/") + 1),
            cover: $(this).find('.dev-cover-img').prop('checked'),
        });
    });

        $('[id$="_media"]').val(JSON.stringify(mediaContainer));
}

function setUploadedImagesCount() {
    $('.dev-image-count').html($('#media-list-target-right .icon-image2').length);
}

function setUploadedVideosCount() {
    $('.dev-video-count').html($('#media-list-target-right .icon-video-camera').length);
}

/**
 * @see http://stackoverflow.com/questions/7753448/how-do-i-escape-quotes-in-html-attribute-values#answer-9756789
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {string} s
 * @param {string} preserveCR
 * @returns {String}
 */
function quoteattr(s, preserveCR) {
    preserveCR = preserveCR ? '&#13;' : '\n';
    return ('' + s) /* Forces the conversion to string. */
        .replace(/&/g, '&amp;') /* This MUST be the 1st replacement. */
        .replace(/'/g, '&apos;') /* The 4 other predefined entities, required. */
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        /*
        You may add other replacements here for HTML only
        (but it's not necessary).
        Or for XML, only if the named entities are defined in its DTD.
        */
        .replace(/\r\n/g, preserveCR) /* Must be before the next replacement. */
        .replace(/[\r\n]/g, preserveCR);
        ;
}

/**
 * add image to the sort view
 * @param {object} media
 */
function addImageToSortView(media) {
    $('.filesUploaded table tbody').append(
        imageSortTemplate
        .replace(/%image-id%/g, media.id)
        .replace(/%image-name%|%title%/g, media.name?media.name:media.vid)
        .replace(/%image-src%/g,media.type == "image" ? '/'+media.imageUrl + '?flushCache=' + encodeURIComponent(new Date().getTime() + Math.random()):media.imageUrl)
        .replace(/%image-caption%/g, quoteattr(media.caption))
        .replace(/%check%/g, media.cover)
        .replace(/%changeDefaultUrl%/g, media.changeCoverUrl)
        .replace(/%image-icon%/g, media.type == "image" ? 'icon-image2' : 'icon-video-camera')
        .replace(/%delete-url%/g, media.deleteUrl)
        .replace(/%is-gif%/g, media.isGif)
        .replace(/%check%/g, media.cover)
        .replace(/%caption-ar%/g, media.captionAr)
        .replace(/%caption-en%/g, media.captionEn)
    );

    setUploadedImagesCount();
    setUploadedVideosCount();
    populateData();

}


//////////////   google search start   /////////////////
/*************init gapi ***************/
function initgapi() {
    gapi.client.setApiKey(G_API_KEY);
    gapi.client.load('youtube', 'v3');
    gapi.client.load('customsearch', 'v1');
}
/********** init gapi end *************/
function showGImageLoading(){
    $('#dev-load-more-gimage').prop('disabled',true).hide();
    $('#dev-loading-more-gimage').show();
}
function hideGImageLoading(){
    $('#dev-load-more-gimage').prop('disabled',false).show();
    $('#dev-loading-more-gimage').hide();
}

function makeRequest(q, googleStart) {
// request limit is 100 queries per day which is very low rate
// more information can be found here : https://console.developers.google.com/project/138808141819/apiui/apiview/customsearch?tabId=quotas
    if (googleStartIndex != 0 && googleStartIndex != 101 && googleStopSearch) {
        q = ((typeof q == "undefined" || q == null) ? googleSearch : q);
        showGImageLoading();
        var request = gapi.client.search.cse.list({
            'q': q,
            'cx': '000148653625495857602:hle-bc_dfj4', //https://www.google.com/cse/all -> create search engin and edit it to accept images -> set Restrict Pages using Schema.org Types to [ImageObject]
            'searchType': 'image',
            'fileType': 'jpg,png,gif',
            'num': 10, // max 10 items per page
            'filter': 0,
            'start': googleStartIndex
        }
        );
        request.then(function(response) {
            hideGImageLoading();
            imageSearch = response;
            appendResults(response);
//            $(".styled, .multiselect-container input").uniform({
//                radioClass: 'choice'
//            });
            $('#dev-google').scrollTop($('#dev-google')[0].scrollHeight - 600);
        }, function(reason) {
            googleStopSearch = 0;
            if ($('.dev-google-result input[type="checkbox"]').length == 0) {
                $('#googlesearchResult').hide();
            }
            if ($('#tab2').find('.alert-danger').length == 0) {
                $('<div class="alert alert-danger"><a class="close" data-dismiss="alert" href="#" aria-hidden="true">×</a>'+messages.googleError+'</div>').insertAfter('#google-hr');
            }
        }
        );
    }
}
//////////////   google image search start   ///////////
function sendRequestToGoogleImageApi() {
    if ($('#dev-search-gimage-box').val().trim() != "") {
        $("#GoogleImportImg-modal").modal('show');
        $('#google-no-result').hide();
        $('#dev-google').html('');
        googleStartIndex = 1;
        googleSearch = $('#dev-search-gimage-box').val().trim();
        if ($("#google-field").hasClass('has-error')) {
            $('#error').remove();
            $("#google-field").removeClass('has-error');
        }
        if (googleStopSearch) {
            makeRequest(googleSearch, googleStartIndex);
        } else {
            $('#googlesearchResult').hide();
        }

    } else {
        $('#google-no-result').hide();
        $('#dev-google').html('');
        if ($('#choose-error').length > 0) {
            $('#choose-error').remove();
        }
        if ($("#google-field").hasClass('has-error')) {
            $('#error').remove();
            $("#google-field").removeClass('has-error');
        }
        $("#google-field").addClass('has-error');
        $("#google-field").append('<div class="help-block help-error" id="error">'+messages.NotBlank+'</div>')
    }
}

function appendResults(response) {
    var pageNo = googleStartIndex;
    if (typeof imageSearch.result.queries.nextPage !== "undefined") {
        googleStartIndex = imageSearch.result.queries.nextPage[0].startIndex;
    } else {
        googleStartIndex = 0;
    }

    if (typeof imageSearch.result.items !== "undefined") {
        $('#dev-google').show();
        var results = imageSearch.result.items;
        var imgContainer = '';
        for (var i = 0; i < results.length; i++) {
            var result = results[i];
            var disabled = '';
            var image_link = (result.link).split("?");
            if (result.image.height < 700 || result.image.width < 1000 || result.image.byteSize > 4194304) {
// || checkExtensionValid(image_link[0])
                disabled = "disabled";
            }
            imgContainer = '<div class="col-lg-2 col-sm-4"><div class="thumbnail"><div class="thumb">'
                          +'<img src="' + result.link + '" alt="' + result.title + '" ></div>'
                          +'<div class="caption"><div class="radio">'
                          +'<label><input type="radio" name="stacked-radio-left" data-url="'+result.link+'" class="styled" >' + result.title.slice(0,10) + '</label>'
                          +'</div></div></div></div>';
            $('#dev-google').append(imgContainer);
                      $(".styled, .multiselect-container input").uniform({
                radioClass: 'choice'
            });

        }
//        checkGoogleHeight();
    }
    else {

        if (pageNo == 1) {
//append error
            $('#googlesearchResult').hide();
            $('#google-no-result').show();
        }

    }
}

function checkGoogleHeight() {
    if ($('#dev-google').scrollTop() < $('#dev-google').height()-100 && googleStartIndex != 0) {
        makeRequest(googleSearch, googleStartIndex);
    }
}

$(document).on('click', '.dev-google-upload', function () {
    $('.cropit-preview-image').removeAttr('src');
    $('.cropit-preview-background').removeAttr('src');
    type = 'upload';
    if ($('#dev-google input:checked').length == 0) {
        showNotificationMsg("يجب اختيار صورة اولا ", "", 'error');
        return false;
    }

    var extension = encodeURI($('#dev-google input:checked').attr('data-url')).substring(($('#dev-google input:checked').attr('data-url')).lastIndexOf('.') + 1);


    if (extension == 'gif') {
        var selectedImage = [];

        $('#dev-google input:checked').each(function () {
            selectedImage.push({
                url: $(this).data('url')

            });
        });
        sendgoogleImageToserver(selectedImage);
         $('.dev-google-crop-spinner').show();
        $('.dev-google-upload').hide();

    } else {
        $('#image-cropper-modal').cropit('imageSrc', corsBroxy + "?url=" + encodeURI($('#dev-google input:checked').attr('data-url')));
        $('#uploadImg').modal('show');
        return false;

    }


});

function sendgoogleImageToserver(selectedImage){
     $.ajax({
            url: googleUploadImage,
            method: "POST",
            data: {
                images: selectedImage,
            },
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
                    showNotificationMsg(data.message, "", 'error');
//                    refreshImages();
                }
                $(".styled, .multiselect-container input").uniform({
                    radioClass: 'choice'
                });
                $('.dev-google-crop-spinner').hide();
                $('.dev-google-upload').show();
            }
        });
}

////////////////   google image search end   /////////////
//////////////   google video search start   ///////////
/******************* youtube ************************/
/**
 * youtube search api
 *
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */

var YT = {
    p: {
        q: "",
        part: "snippet",
        maxResults: 9,
        type: "video",
        pageToken: "",
    },
    totalCount: 0,
    disabled: false,
    blocked: false,
    container: $('.videossearch-results-list .search-results-list'),
    next: function() {
        if (this.container.scrollTop() + this.container.innerHeight() >= this.container[0].scrollHeight && !this.blocked && !this.disabled)
        {
            //to trigger the infinit scrolling function
            this.blocked = true;
            this.submit();
        }
    },
    reset: function(emptyQueryField) {
        $('#youtube-no-result').hide();
        $('.videossearch-results-list .search-results-list').hide();
        typeof emptyQueryField == "boolean" ? emptyQueryField : true;
        this.p = {
            q: "",
            part: "snippet",
            maxResults: 9,
            type: "video",
            pageToken: "",
        };
        this.totalCount = 0,
        this.disabled = false;
        this.blocked = false;
        this.container.empty();

        if (emptyQueryField)
            $('#dev-search-gvideo-box').val("");

        this.removeError();
    },
    submit: function() {

        $("#GoogleImportVideos-modal").modal('show');

        var request = gapi.client.youtube.search.list(this.p);

        this.appendSpinner();

        request.execute(function(response) {

            YT.p.pageToken = response.nextPageToken;
            YT.totalCount = response.pageInfo.totalResults;

            var result = "";
            var binded = 0;

            YT.removeSpinner();

            $.each(response.items, function(i) {
                if ($.inArray(this.id.videoId, YT.binded()) == -1) {
                    result += videoSearchTemplate
                            .replace(/%video-id%/g, this.id.videoId)
                            .replace(/%video-channel%/g, this.snippet.channelTitle)
                            .replace(/%video-date%/g, this.snippet.publishedAt.split("T").shift())
                            .replace(/%video-desc%/g, this.snippet.description.slice(0,100) + this.snippet.description.length > 100?"...":"")
                            .replace(/%video-title%/g, this.snippet.title)
                            .replace(/%video-thumb%/g, this.snippet.thumbnails.high.url);
                } else {
                    binded++;
                }

            });
            if (result != "") {
                $('.videossearch-results-list .search-results-list').append(result);
            } else {
                $('.videossearch-results-list .search-results-list').hide();
                $('#youtube-no-result').show();
            }

            YT.blocked = false;

            if (typeof response.nextPageToken != "undefined" && (binded > 0 || $('.videossearch-results-list .search-results-list').children().length < YT.p.maxResults)) {
                YT.next();
            }
        });
    },
    checked: function() {
        return $('.videossearch-results-list li input:checked').map(function() {
            return this.value;
        });
    },
    binded: function() {
        return $('#video-tab3 .handles.list').first().find('li').map(function() {
            return $(this).attr('dev-video-id');
        }).toArray();
    },
    uncheck: function() {
        $('.videossearch-results-list li input:checked').prop('checked', false);
    },
    appendSpinner: function() {
        $('#youtube-no-result').hide();
        $('.videossearch-results-list .search-results-list').show();
        if ($('.infinite-spinner-container').length > 0)
            return;
        var spinner = $('<div>').addClass("col-md-12 infinite-spinner-container")
                .append(
                        $('<div>').addClass('infinite-spinner text-center')
                        .append($('<i>').addClass('fa fa-spinner fa-spin loading-spinner')
                                )
                        );
        this.container.append(spinner);

    },
    removeSpinner: function() {
        $('#video-tab1 .infinite-spinner-container').remove();
    },
    removeError: function() {
        $('#video-tab1 .help-error').remove();
        $('#error').remove();
        $("#dev-search-gvideo-box").parent().removeClass('has-error');
    }
};


$('.videossearch-results-list .search-results-list').on('scroll', function() {
    YT.next();
});


//give scrollable class to scrollable div to disable main page scrolling after reaching the end of it

if (!/Android|webOS|iPhone|iPod|BlackBerry/i.test(navigator.userAgent)) {
    $('.Scrollable').on('DOMMouseScroll mousewheel', function(ev) {
        var $this = $(this),
                scrollTop = this.scrollTop,
                scrollHeight = this.scrollHeight,
                height = $this.height(),
                delta = (ev.type == 'DOMMouseScroll' ?
                        ev.originalEvent.detail * -40 :
                        ev.originalEvent.wheelDelta),
                up = delta > 0;

        var prevent = function() {
            ev.stopPropagation();
            ev.preventDefault();
            ev.returnValue = false;
            return false;
        }

        if (!up && -delta > scrollHeight - height - scrollTop) {
            // Scrolling down, but this will take us past the bottom.
            $this.scrollTop(scrollHeight);
            return prevent();
        } else if (up && delta > scrollTop) {
            // Scrolling up, but this will take us past the top.
            $this.scrollTop(0);
            return prevent();
        }
    });
}

/**
 * refresh the sort view from the server
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function refreshMediaSortView() {
    $('#media-list-target-right').html('');
//    setUploadedImagesCount();
    $.ajax({
        url: refreshImagesUrl,
        success: function (data) {
            for (var i = 0; i < data.images.length; i++) {
                addImageToSortView(data.images[i]);
            }
            $(".styled, .multiselect-container input").uniform({
                radioClass: 'choice'
            });
            populateData();
            setUploadedImagesCount();
            setUploadedVideosCount();
            selectedCover = $('input.dev-cover-img:checked').val()?$('input.dev-cover-img:checked').val():null;
            $('#recipe_defaultCoverPhoto').val($('input.dev-cover-img:checked').val())
            $('a[data-popup="popover"]').popover({
                delay:{ "hide": 500 }
            });
        }
    });
}
/************ youtube ************/
//////////////   google video search end   /////////////
//////////////   google search end   ///////////////////

    var delay = (function() {
        var timer = 0;
        return function(callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();


/**
 * @author ahmad Gamal <a.gamal@ibtikar.net.sa>
 */
function valid_youtubeUrl(videoUrl) {
    return videoUrl.match(/^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/);
}
/**
 * @author ahmad Gamal <a.gamal@ibtikar.net.sa>
 * on keyup only if the key is a letter or number only + backspace
 */
function isValidKey(keyCode) {
    var invalidKeysCodes = [9, 32, 16, 17, 18, 20, 37, 38, 39, 40, 92,
        144, 122, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123];

    if($.inArray(keyCode, invalidKeysCodes) !== -1) return false;
}
function updateGallaryType(){
    $('[name="dev-gallary-type"]').each(
            function(){
                $(this).prop('checked',$(this).val() == $('[id$="_galleryType"]').val());
            });
    $.uniform.update();
}

/**
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 */
function updateCheckedCover() {
    if (selectedCover) {
        $('input[type="radio"][value="' + selectedCover + '"]').prop('checked', true);
    }
}

jQuery(document).ready(function($) {

    drake.on('drop',function(elm){
        setTimeout(function(){
            updateCheckedCover();
        },0);
    });

    $(document).on('change', '.dev-cover-img', function() {
        if($(this).is(':checked')){
            $('[id$="_defaultCoverPhoto"]').val($(this).val());
            selectedCover = $(this).val();
        }else{
            $('[id$="_defaultCoverPhoto"]').val("");
        }
    });
    $(document).on('click', '.dev-upload-recipe-img', function() {
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

    $('body').on('preAjaxCallback',function(){
        populateData();
        $('#recipe_galleryType').val($('input.dev-gallary-type:checked').val())
        if(!$('.dev-cover-img').is(':checked')){
            showNotificationMsg("يجب إختيار صورة غلاف", "", "error");
            return false;
        }else{
            updateMinRelated();
            if($('#recipe_minvalue').val()==''){
                showNotificationMsg("يجب الا تقل المقالات والنصائح ذات صله عن 2", "", "error");
                return false;
            }
        }
    });

    $(document).on('click', '#search-vid-btn', function(e) {
        e.preventDefault();
        $('.videossearch-results-list .search-results-list').scrollTop(0);
        var query = $('#dev-search-gvideo-box').val();
        if (query.trim() != "") {
            if (!YT.blocked && !YT.disabled) {
                YT.reset(false);
                YT.blocked = true;
                YT.p.q = query;
                YT.submit();
            }
        } else {
            YT.reset();
            YT.removeError();
            $("#dev-search-gvideo-box").parent().addClass('has-error');
            $("#dev-search-gvideo-box").parent().append('<div class="help-block help-error" id="error">يجب إدخال القيمة</div>');
        }

    });

    $('.dev-youtube-submit').on('click', function() {

        var selectedVideos = YT.checked();

        if ($("#dev-search-gvideo-box").parent().hasClass('has-error')) {
            $('#error').remove();
            $("#dev-search-gvideo-box").parent().removeClass('has-error');
        }

        if (selectedVideos.length == 0 && $('#video-tab1 .searchItem').length != 0) {
            console.log('hnaa')
            $('#video-tab1 .help-error').remove();
            $('<div class="help-block help-error" style="margin-bottom: 10px;color:#a94442">'+messages.pleaseSelectOneVideo+'</div>').insertAfter('#youtube-hr');
        } else if (selectedVideos.length == 0 && $('#video-tab1 .searchItem').length == 0) {
             showNotificationMsg("الرجاء اختيار فيديو", "", "error");
            return false;
        } else {
            $('.dev-youtube-submit').attr('disabled','disabled');

            populateData();

            $.ajax({
                url: youtubeUploadVideo,
                method: "POST",
                data: {
                    videos: selectedVideos.toArray()
                },
                success: function(data) {
                    if (data.status === 'success') {
                        YT.uncheck();
                        showNotificationMsg(data.message, "", data.status);
                        YT.reset(true);

                        $(data.video).each(function () {
                            addImageToSortView(this);
                        });
                        $(".styled, .multiselect-container input").uniform({
                            radioClass: 'choice'
                        });

                        $('#GoogleImportVideos-modal').modal('hide')

                    }

                    $('a[data-popup="popover"]').popover({
                        delay:{ "hide": 500 }
                    });

                    $('.dev-youtube-submit').removeAttr('disabled');
                }
            });
        }
    });

    $('.click-on-enter').on('keyup',function(e) {
                if (e.which === 13) {
                    $(this).parent().next().find('button').trigger('click');
                    return false;
                }
           });
    $(document).on('click', '#search-img-btn-small', function() {
        if ($('#dev-search-gimage-box-small').val().trim() != "") {
            $('#dev-search-gimage-box').val($('#dev-search-gimage-box-small').val());
            sendRequestToGoogleImageApi();
        }
    });

    $(document).on('click', '#search-vid-btn-small', function() {
        if ($('#dev-search-gvideo-box-small').val().trim() != "") {
            $('#dev-search-gvideo-box').val($('#dev-search-gvideo-box-small').val());
            $('#search-vid-btn').trigger('click');
        }
    });

    $(document).on('click', '#search-img-btn', function() {
        sendRequestToGoogleImageApi();
    });
    $(document).on('click', '#dev-load-more-gimage', function() {
        makeRequest(googleSearch, googleStartIndex);
    });

    $(document).on('keyup', '#dev-search-gimage-box,#dev-search-gimage-box-small,#dev-search-gvideo-box,#dev-search-gvideo-box-small', function (e) {
        e.preventDefault();

        var parentElm = $(this).parents(".input-group");
        if ($(this).val().trim() != "") {
            if (parentElm.hasClass('has-error')) {
                parentElm.removeClass('has-error');
                $("#error").remove();
            }
        } else {
            if (parentElm.hasClass('has-error')) {
                $("#error").remove();
                parentElm.removeClass('has-error');
            }
            parentElm.addClass('has-error');
            parentElm.after('<div class="help-block help-error redText" id="error">'+messages.NotBlank+'</div>')
        }
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
                        closestTr.remove();
                        showNotificationMsg(data.message, "", data.status);
                    }else{
                        showNotificationMsg(imageErrorMessages.generalError, "", 'error');
                        refreshImages();
                    }
                    setUploadedImagesCount();
                    setUploadedVideosCount();
                    tdLoadingToggle(closestTd);
                }
            });
    });

    $('#GoogleImportImg-modal').on('hidden.bs.modal', function(e) {
        $('#dev-search-gimage-box,#dev-search-gimage-box-small').val("");
    });
    $('#GoogleImportVideos-modal').on('hidden.bs.modal', function(e) {
        YT.reset();
//        $('#dev-search-gvideo-box,#dev-search-gvideo-box-small').val("");
    });

// external source start

    $(document).on('click', '.dev-imageurl-submit', function () {
        var errorContainer = $('.dev-recipe-imgeUrl-error');
        var obj = $('input.dev-recipe-imgeUrl').val();
        var imageSrc = $.trim(obj);
        var errorContainer = $('.dev-recipe-imgeUrl-error');
        if (imageSrc.length > 0) {
            var extension = encodeURI(imageSrc).substring((imageSrc).lastIndexOf('.') + 1);
            if (extension == 'gif') {
                var selectedImage = [];
                    selectedImage.push({
                        url: imageSrc

                });

                sendgoogleImageToserver(selectedImage);
            } else {

                $('.dev-imageurl-submit').attr('disabled', 'disabled');
                $.ajax({
                    url: uploadImageUrl,
                    method: 'post',
                    data: {'imageUrl': imageSrc},
                    success: function (data) {
                        if (data == 'error') {
                            showNotificationMsg(messages.wrongURL, '', 'error');
                        }
                        if (data == 'errorImageExtension') {
                            showNotificationMsg(messages.imageTypeError, '', 'error');
                        }
                        if (data == 'errorImageSize') {
                            showNotificationMsg(messages.imageDimensionsError, '', 'error');
                        }
                        if (data == 'errorImageFileSize') {
                            showNotificationMsg(messages.largeImageError, '', 'error');
                            $('.dev-recipe-imgeUrl').val('');
                        }
                        if (data == 'success') {
                            type = 'upload';
                            $('.cropit-preview-image').removeAttr('src');
                            $('.cropit-preview-background').removeAttr('src');
                            $('#image-cropper-modal').cropit('imageSrc', corsBroxy + "?url=" + encodeURI(imageSrc));
                            $('#uploadImg').modal('show');
//                                return false;
//            $('.dev-imageurl-submit').attr('disabled','disabled');
//            var imageUrl = [];
//            imageUrl.push({url: imageSrc});
//
//            if (imageUrl.length != 0) {
//                $.ajax({
//                    url: googleUploadImage,
//                    method: "POST",
//                    data: {images: imageUrl},
//                    success: function(data) {
//                        $('.dev-recipe-imgeUrl').val('');
//
//                        if(typeof data.errors != "undefined" && data.errors.length != 0){
//                            $.each(data.errors,function(key){
//                                showNotificationMsg(key,'error');
//                            });
//                        }else if (data.status === 'success') {
//
//                            for (var i = 0; i < data.files.length; i++) {
//                                addImageToSortView(data.files[i]);
//                            }
//                            showNotificationMsg(data.message, "", data.status);
//                        }
//                        $('.dev-imageurl-submit').removeAttr('disabled');
//                    }
//                });
//            }

                        }
                        $('.dev-imageurl-submit').removeAttr('disabled');
                        $('.dev-recipe-imgeUrl').val('');

                    }
                });

            }
        }
    });
///////////////////////////////////

    $(document).on('click', '.dev-videourl-submit', function() {


//////////////////////////////////////////////////////////////////////
            if(typeof ytXhr !== "undefined")
                ytXhr.abort();


            var obj = $('input.dev-recipe-videoUrl').val();
            var videoUrl = $.trim(obj);

            if (videoUrl === "") {
                errorContainer.removeClass('has-error');
                $('.dev-videoUrl-overlay').hide();
                return;
            }

            if (!valid_youtubeUrl(videoUrl)) {
                showNotificationMsg(messages.wrongURL,'','error');
                $('.dev-recipe-videoUrl').val('');
                return;
            }

            var videoId = videoUrl.substr(videoUrl.indexOf("=") + 1);

            if (videoId.indexOf("https://www.youtube") > -1) {
                showNotificationMsg(messages.wrongURL,'','error');
                $('.dev-recipe-videoUrl').val('');
                return;
            }

            ytXhr = $.ajax({
                url: validateVideoUrl,
                method: 'post',
                data: {'videoUrl': videoUrl},
                success: function(data) {
                    if (data == 'error') {
                        showNotificationMsg(messages.wrongURL,'','error');
                    } else {
                            $('.dev-videourl-submit').attr('disabled','disabled');
                            var videos = [];
                            videos[0] = videoId;

                            $.ajax({
                                url: youtubeUploadVideo,
                                method: "POST",
                                data: {videos: videos},
                                success: function(data) {
                                    if (data.status === 'success') {
                                        showNotificationMsg(data.message, "", data.status);
                                        $(data.video).each(function () {
                                            addImageToSortView(this);
                                        });
                                    }

                                    $('.dev-recipe-videoUrl').val('');
                                    $('.dev-videourl-submit').removeAttr('disabled');
                                }
                            });

                    }
                }
            });
//////////////////////////////////////////////////////////////////////
    });







// external source end


    refreshMediaSortView();
    updateGallaryType();
    setUploadedImagesCount();
    setUploadedVideosCount();
});