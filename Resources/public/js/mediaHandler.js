var mediaContainer = {
    images: [
//        {
//            id   :"",
//            order:"",
//            path:"",
//            caption:""
//        }
    ],
    videos: [
        //        {
        //            id   :"",
        //            order:"",
        //            vid:"",
        //            caption:""
    ]
};

var selectedCover = null;
var retryLimit = 3;
var retries = 0;


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
////////////////   google image search end   /////////////
//////////////   google video search start   ///////////
//////////////   google video search end   /////////////
//////////////   google search end   ///////////////////

jQuery(document).ready(function($) {

    $(document).on('click', '#search-img-btn-small', function() {
        if ($('#dev-search-gimage-box-small').val().trim() != "") {
            $('#dev-search-gimage-box').val($('#dev-search-gimage-box-small').val());
            sendRequestToGoogleImageApi();
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
            parentElm.append('<div class="help-block help-error" id="error">'+messages.NotBlank+'</div>')

        }
    });

    $('#GoogleImportImg-modal').on('hidden.bs.modal', function(e) {
        $('#dev-search-gimage-box,#dev-search-gimage-box-small').val("");
    });
});