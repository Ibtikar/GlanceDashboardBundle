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
            .replace(/%image-name%|%title%/g, media.path)
            .replace(/%image-src%/g,media.type == "image" ? '/'+media.imageUrl + '?flushCache=' + encodeURIComponent(new Date().getTime() + Math.random()):media.imageUrl)
            .replace(/%image-caption%/g, quoteattr(media.caption))
            .replace(/%check%/g, media.cover)
            .replace(/%image-icon%/g, media.type == "image" ? 'icon-image2' : 'icon-video-camera')
            .replace(/%delete-url%/g, media.deleteUrl)
            .replace(/%is-gif%/g, media.isGif)
            .replace(/%caption-ar%/g, media.captionAr)
            .replace(/%caption-en%/g, media.captionEn)
            );
//    checkImageCanBeCropped(media.id, media.path + '?flushCache=' + encodeURIComponent(new Date().getTime() + Math.random()));
//    setUploadedImagesCount();
//    initSortable('#tab5');
//    checkSelectedAllImages();
//    if ($('.dev-delete-image-checkbox').length > 0) {
//        $('.dev-check-images-all').closest('.row').show();
//    } else {
//        $('.dev-check-images-all').closest('.row').hide();
//    }
//    if ($('.dev-default-photo-sort:checked').length > 0) {
//        $('.dev-default-photo-sort').change();
//    }
//    populateData();
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
            $(".styled, .multiselect-container input").uniform({
                radioClass: 'choice'
            });
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
            if (result.image.height < 200 || result.image.width < 200 || result.image.byteSize > 4194304) {
// || checkExtensionValid(image_link[0])
                disabled = "disabled";
            }
            imgContainer = '<div class="col-lg-2 col-sm-4"><div class="thumbnail"><div class="thumb">'
                          +'<img src="' + result.link + '" alt="' + result.title + '" ></div>'
                          +'<div class="caption"><div class="radio">'
                          +'<label><input type="radio" name="stacked-radio-left" data-url="'+result.link+'" class="styled" >' + result.title.slice(0,10) + '</label>'
                          +'</div></div></div></div>';
            $('#dev-google').append(imgContainer);
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

$(document).on('click','.dev-google-upload', function() {
    $('#image-cropper-modal').cropit('imageSrc',corsBroxy + "?url=" + encodeURI($('#dev-google input:checked').attr('data-url')));
    $('#uploadImg').modal('show');
    return false;
    var selectedImage = [];
    if ($('#dev-google input:checked').length == 0) {
        if ($('.dev-google-result').find('.help-block.help-error').length == 0) {
            $('<div class="help-block help-error" id="choose-error" style="margin-bottom: 10px;color:#a94442">'+messages.pleaseSelectOneImage+'</div>').insertAfter('#google-hr');
        }
    } else if ($('#dev-google input:checked').length == 0 && $('#dev-google').children().length == 0) {
        if ($("#google-field").hasClass('has-error')) {
            $('#error').remove();
            $("#google-field").removeClass('has-error');
        }
        $("#google-field").addClass('has-error');
        $("#google-field").append('<div class="help-block help-error" id="error">'+messages.searchBeforeUpload+' </div>')

    } else {
        $('.dev-google-upload').attr('disabled','disabled');
        if ($("#google-field").hasClass('has-error')) {
            $('#error').remove();
            $("#google-field").removeClass('has-error');
        }
        $('#dev-google input:checked').each(function() {
            selectedImage.push({
                url: $(this).data('url')

            });
        });
        $('.img-upload-spinner').show();
        var noOfSelectedImage = $('#dev-google input:checked').length;
        $.ajax({
            url: googleUploadImage,
            method: "POST",
            data: {
                images: selectedImage,
            },
            success: function(data) {
                if (data.status === 'success') {
                    googleStartIndex = 0;
                    $('#dev-google').html('');
                    $('#googlesearchResult').scrollTop(0);
                    $('#googlesearchResult').hide();
                    $('#searchbox').val('');
                    googleSearch = '';
                    if (data.success) {
                        showNotificationMsg(messages.uploadSuccessfuly);
                    } else if (!data.success && data.errors) {
                        showNotificationMsg(messages.notDone, 'error');
                    }

                    if (!$.isEmptyObject(data.errors)) {
                        $('#googlesearchResult').show();
                    }
                    for (message in data.errors) {
                        for (index in data.errors[message]) {
                            var imgContainer = '<div class="col-md-3 col-sm-6 col-xs-4 googleImgItem">'
                                    + '<div class="errorMessage">' + message + '</div>'
                                    + ' <div class="searchItem"><span class="inputWrapper">'
                                    + '<input type="checkbox" value="None"  name="check" class="check dev-check-image" data-url="' + data.errors[message][index] + '" disabled />'
                                    + '</span><img src="' + data.errors[message][index] + '" class="img-responsive" />'
                                    + '</div></div>';
                            $('#dev-google').append(imgContainer);
                        }

                    }
                    $('.img-upload-spinner').hide();
                    if(data.files) {
                        for (var i = 0; i < data.files.length; i++) {
                            addImageToSortView(data.files[i]);
                        }
                    }
                    setUploadedImagesCount();
                }

                $('.dev-google-upload').removeAttr('disabled');
            }
        });
    }
});



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
        success: function(data) {
            for (var i = 0; i < data.images.length; i++) {
                addImageToSortView(data.images[i]);
            }
            populateData();
            setUploadedImagesCount();
            setUploadedVideosCount();
            $('a[data-popup="popover"]').popover({
                delay:{ "hide": 500 }
            });
        }
    });
}
/************ youtube ************/
//////////////   google video search end   /////////////
//////////////   google search end   ///////////////////

function updateGallaryType(){
    $('[name="dev-gallary-type"]').each(
            function(){
                $(this).prop('checked',$(this).val() == $('[id$="_galleryType"]').val());
            });
    $.uniform.update();
}

jQuery(document).ready(function($) {

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
        console.log(selectedVideos)

        if ($("#dev-search-gvideo-box").parent().hasClass('has-error')) {
            $('#error').remove();
            $("#dev-search-gvideo-box").parent().removeClass('has-error');
        }

        if (selectedVideos.length == 0 && $('#video-tab1 .searchItem').length != 0) {
            $('#video-tab1 .help-error').remove();
            $('<div class="help-block help-error" style="margin-bottom: 10px;color:#a94442">'+messages.pleaseSelectOneVideo+'</div>').insertAfter('#youtube-hr');
        } else if (selectedVideos.length == 0 && $('#video-tab1 .searchItem').length == 0) {
            $("#dev-search-gvideo-box").parent().addClass('has-error');
            $("#dev-search-gvideo-box").parent().append('<div class="help-block help-error" id="error">'+messages.searchBeforeUpload+' </div>');

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
                        addImageToSortView(data.video);
                        $('#GoogleImportVideos-modal').modal('hide')

                    }

                    $('.dev-youtube-submit').removeAttr('disabled');
                }
            });
        }
    });

    $(document).on('keyup', '#dev-search-gvideo-box', function() {
        if ($('#dev-search-gvideo-box').val().trim() != "") {
            YT.removeError();
        } else {
            YT.removeError();
            $("#dev-search-gvideo-box").parent().addClass('has-error');
            $("#dev-search-gvideo-box").parent().append('<div class="help-block help-error" id="error">'+messages.NotBlank+'</div>');
        }


    })
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
    $(document).on('keyup', '#dev-search-gimage-box,#dev-search-gimage-box-small', function() {
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
//
// external source end


    refreshMediaSortView();
    updateGallaryType();
    setUploadedImagesCount();
    setUploadedVideosCount();
});