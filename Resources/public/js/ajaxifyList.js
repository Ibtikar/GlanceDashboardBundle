var addToContactsFormData = [];

/**
 * @returns {BaseList}
 */
function BaseList() {

    /**
     * @param {object} clickedElement
     */
    this.confirmDeleteOne = function (clickedElement) {
        deleteOneParams = {id: clickedElement.attr("data-id")};
        var status = clickedElement.attr("data-status")
        if (window.location.pathname.match(/studio\/[a-zA-Z]+\/list/)) {
            if(status.indexOf('autopublish') != -1){
               showConfirmationBox(listMessages.deleteConfirmMessageAutoPublish.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem).replace('%type%', itemType).replace('%gender%', clickedElement.attr('data-gender')), deleteOne, listMessages.deleteOneModalTitle.replace('%one', itemType), hideLoader);
            }else{
               showConfirmationBox(listMessages.deleteOneTypeConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem).replace('%type%', itemType).replace('%gender%', clickedElement.attr('data-gender')), deleteOne, listMessages.deleteOneModalTitle.replace('%one', itemType), hideLoader);
            }
        } else {
            showConfirmationBox(listMessages.deleteOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), deleteOne, listMessages.deleteOneModalTitle.replace('%one', oneItem), hideLoader);
        }
    }

    this.confirmDeleteOneAutoPublished = function (clickedElement) {
        deleteOneParams = {id: clickedElement.attr("data-id")};
        var status = clickedElement.attr("data-status");
        if (status.indexOf('autopublish') != -1) {
            showConfirmationBox(listMessages.deleteConfirmMessageAutoPublish.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), deleteOne, listMessages.deleteOneModalTitle.replace('%one', oneItem), hideLoader);
        } else {
            showConfirmationBox(listMessages.deleteOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), deleteOne, listMessages.deleteOneModalTitle.replace('%one', oneItem), hideLoader);
        }
    }

    this.confirmDeleteBulk = function () {
        var selectedItemsNumber = $('tbody .dev-list-check:checked').length;
        var flag = true;
        $('tbody .dev-list-check:checked').each(function(){
            if(typeof $(this).attr('data-status') != 'undefined'){
                if($(this).attr('data-status').indexOf('autopublish') == -1){
                    flag = false;
                    return false;
                }
            }
            else{
                flag = false;
                return false;
            }
        });
        if(flag){
            showConfirmationBox(listMessages.deleteManyConfirmMessageAutoPublish.replace('%s', selectedItemsNumber).replace('%itemCountLabel', manyItemsLabel), bulkFunction, listMessages.deleteManyModalTitle.replace('%many', manyItems), function () {
                $(".dev-bulk-action-select").select2('val', '');
            });
        }else{
            if (window.location.pathname.match(/studio\/[a-zA-Z]+\/list/)) {

                    showConfirmationBox(listMessages.deleteManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemType), bulkFunction, listMessages.deleteManyModalTitle.replace('%many', itemsType), function () {
                      $(".dev-bulk-action-select").select2('val', '');
                    });
            } else {
                showConfirmationBox(listMessages.deleteManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), bulkFunction, listMessages.deleteManyModalTitle.replace('%many', manyItems), function () {
                    $(".dev-bulk-action-select").select2('val', '');
                });
            }
        }
    }

    this.confirmChangeActiveStatus = function (clickedElement) {
        changeStatusParams = {id: clickedElement.attr("data-id"), status: clickedElement.attr("data-status")};
        status = clickedElement.attr('data-status');
        if (status === 'false') {
            showConfirmationBox(listMessages.deactivateOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), toggleActiveStatus, listMessages.deactivateOneModalTitle);
        } else {
            showConfirmationBox(listMessages.activateOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), toggleActiveStatus, listMessages.activateOneModalTitle);
        }
    }

    this.confirmChangeStopAnswer = function (clickedElement) {
        changeStatusParams = {id: clickedElement.attr("data-id"), status: clickedElement.attr("data-status")};
        status = clickedElement.attr('data-status');
        if (status === 'false') {
            showConfirmationBox(listMessages.stopOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), toggleActiveStatus, listMessages.stopOneModalTitle);
        } else {
            showConfirmationBox(listMessages.resumeOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), toggleActiveStatus, listMessages.resumeOneModalTitle);
        }
    }

    this.confirmChangeActiveStatusBulk = function (targetStatus) {
        var selectedItemsNumber = $('tbody .dev-list-check:checked').length;
        if (targetStatus === 'Activate') {
            showConfirmationBox(listMessages.activateManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), bulkFunction, listMessages.activateManyModalTitle, function () {
                $(".dev-bulk-action-select").select2('val', '');
            });
        } else if (targetStatus === 'Deactivate') {
            showConfirmationBox(listMessages.deactivateManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), bulkFunction, listMessages.deactivateManyModalTitle, function () {
                $(".dev-bulk-action-select").select2('val', '');
            });
        }
    }

    this.confirmChangePublish = function (clickedElement) {
        changePublishParams = {id: clickedElement.attr("data-id"), publish: clickedElement.attr("data-publish")};
        publish = clickedElement.attr('data-publish');
        if (publish === 'false') {
            showConfirmationBox(listMessages.unpublishOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), togglePublish, listMessages.unpublishOneModalTitle);
        } else {
            if (typeof clickedElement.attr('data-status')!='undefined' && clickedElement.attr('data-status').toLowerCase().indexOf("autopublish") >= 0) {
                showConfirmationBox(listMessages.autopublishConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), togglePublish, listMessages.publishOneModalTitle);
            } else {
                showConfirmationBox(listMessages.publishOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), togglePublish, listMessages.publishOneModalTitle);

            }
        }
    }

    this.confirmPublishEvent = function (clickedElement) {
        publishParams = {id: clickedElement.attr("data-id"), publish: clickedElement.attr("data-publish"), room: clickedElement.attr("data-room")};
        calledFrom = clickedElement.attr("data-page");
        if(clickedElement.attr('data-status').toLowerCase().indexOf("autopublish") >= 0){
            showConfirmationBox(listMessages.autopublishConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), publishEvent, listMessages.publishOneModalTitle.replace('%one', oneItem));
        }else{
          showConfirmationBox(listMessages.publishOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), publishEvent, listMessages.publishOneModalTitle.replace('%one', oneItem));

        }
    }

    this.confirmResendMail = function (clickedElement) {
        resendParameter = {id: clickedElement.attr("data-id")};
        showConfirmationBox(listMessages.resendMailConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), resendMail, listMessages.resendMailModalTitle.replace('%one', oneItem));

    }

    this.showMoveModal = function (clickedElement) {
        var basicModal = new BasicModal();
        var currentActionUrl = moveUrl;
        var params = {};
        if (clickedElement) {
            params['id'] = clickedElement.attr("data-id");
            params['isAlbumForm'] = clickedElement.attr("data-is-album-form");
        }
        params['oldAlbumId'] = oldalbumId;

        basicModal.show(currentActionUrl, function () {
            $(".modal-body select").select2();

            $('#dev-albums-select').on("select2-close", function () {
                $('#basicModal').attr('tabindex', '-1');
            }).on("select2-open", function () {
                $('#basicModal').removeAttr('tabindex');
            });

            $('.dev-move-button').click(function () {
                if ($('#dev-move-form').valid()) {
                    if (clickedElement) {
                        thisObject.moveToAlbum(clickedElement, basicModal);
                    } else {
                        var moveAttrs = {
                            album: $('#dev-albums-select').val(),
                            isAlbumForm: $("#isAlbumForm").val()
                        };
                        if ($("#isAlbumForm").val() == "true") {
                            moveImageFromAlbumForm(moveAttrs, basicModal);
                        } else {
                            bulkFunction("", moveAttrs, basicModal);
                        }
                    }
                } else {
                    $('.error').addClass('help-block');
                    $('.error').parent().parent('.form-group').addClass('has-error');
                }
            });
        }, params);
        basicModal.onHide(function () {
            $(".dev-bulk-action-select").select2('val', '');
        });
    }


    this.moveToAlbum = function (clickedElement, basicModal) {
        var currentActionUrl = moveUrl;
        if ($('#dev-move-form').valid()) {
            postData = {
                album: $('#dev-albums-select').val(),
                isAlbumForm: clickedElement.attr("data-is-album-form"),
                oldAlbumId: clickedElement.attr("data-old-album-id"),
                id: clickedElement.attr("data-id")
            }
            var numOfRecords = $('tr[data-id]').length;
            var pageNum = getQueryVariable('page');
            $.ajax({
                url: currentActionUrl,
                method: 'POST',
                data: postData,
                success: function (data) {
                    if (data.status !== "success") {
                        $('#dev-move-error-message').html(data.message);
                        $('#dev-move-error-message').show();
                    } else {
                        basicModal.hide();
                        if (clickedElement.attr("data-is-album-form") == "true") {
                            showNotification(messages.movedSuccessfuly);
                            refreshImageSortView();
                        } else {
                            if (pageNum !== 1 && numOfRecords === 1) {
                                retunToPreviousPage(pageNum);
                            } else {
                                stateChangeHandler();
                            }
                        }
                    }
                }
            });
        }
    }

    this.confirmChangePublishBulk = function (targetStatus) {
        var selectedItemsNumber = $('tbody .dev-list-check:checked').length;
        if (targetStatus === 'Publish') {
            showConfirmationBox(listMessages.publishManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), bulkFunction, listMessages.publishManyModalTitle, function () {
                $(".dev-bulk-action-select").select2('val', '');
            });
        } else if (targetStatus === 'Unpublish') {
            showConfirmationBox(listMessages.unpublishManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), bulkFunction, listMessages.unpublishManyModalTitle, function () {
                $(".dev-bulk-action-select").select2('val', '');
            });
        }
    }

    this.showColumnOptionsModal = function () {
        var basicModal = new BasicModal();
        basicModal.show(changeListColumnsUrl, function () {
            $(".dev-columns-multi-select").pickList({sortItems: false, sourceListLabel: listMessages.multiSelectSourceListLabel, targetListLabel: listMessages.multiSelectTargetListLabel});
            $(".dev-save-columns").click(function () {
                saveListSelectedColumns(basicModal, changeListColumnsUrl);
            });
        });
    }

    this.autopublish = function (autopublishUrl, clickedElement, basicModal) {
        if (!$('#dev-publish-form').valid()) {
            return;
        }
        showConfirmationBox(listMessages.publishOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem),
                function () {
                    showLoader();
                    postData = {
                        sendPushNotification: $('#sendPushNotification:checked').val(),
                        breakingNewColor: $('#breakingNewColor').val(),
                        id: clickedElement.attr('data-id'),
                        autoPublishDate: $('#material_publish_time').val(),
                        action: clickedElement.attr('data-action')
                    };
                    $.ajax({
                        url: autopublishUrl,
                        method: 'POST',
                        data: postData,
                        success: function (data) {
                            if (data.status === 'error') {
                                hideLoader();
                                $('#dev-publish-error-message').html(data.message);
                                $('#dev-publish-error-message').show();
                            } else {
                                basicModal.hide();
                                var numOfRecords = $('tr[data-id]').length;
                                var pageNum = getQueryVariable('page');
                                if (calledFrom === 'list') {
                                    if (pageNum !== 1 && numOfRecords === 1) {
                                        retunToPreviousPage(pageNum);
                                    } else {
                                        stateChangeHandler();
                                    }
                                } else if (calledFrom === 'view') {
                                    window.location = roomListUrl;
                                }
                            }
                        }
                    });
                }, listMessages.publishOneModalTitle.replace('%one', oneItem));
    };

    this.viewAutoPublishModal = function (clickedElement) {
        var basicModal = new BasicModal();
        basicModal.show(autopublishUrl, function () {
            $('.dev-publish-material-button').click(function () {
                setDateTime();
                if (!$('#dev-publish-form').valid()) {
                    return;
                }
                thisObject.autopublish(autopublishUrl, clickedElement, basicModal);
            });
        }, {'id': clickedElement.attr('data-id'), 'action': clickedElement.attr('data-action')});
    };


    this.confirmChangeFavoriteStatus = function (clickedElement) {
        changeStatusFavoriteParams = {id: clickedElement.attr("data-id"), status: clickedElement.attr("data-status")};
        status = clickedElement.attr('data-status');
        if (status === 'true') {
            toggleFavoriteStatus();
           // showConfirmationBox(listMessages.unfavoriteOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), toggleFavoriteStatus, listMessages.unfavoriteOneModalTitle);
        } else {
            toggleFavoriteStatus();
          //  showConfirmationBox(listMessages.favoriteOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem), toggleFavoriteStatus, listMessages.favoriteOneModalTitle);
        }
    }


    this.confirmChangeFavoriteStatusBulk = function (targetStatus) {
        var selectedItemsNumber = $('tbody .dev-list-check:checked').length;
        if (targetStatus === 'Favorite') {
            showConfirmationBox(listMessages.favoriteManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), bulkFunction, listMessages.favoriteManyModalTitle, function () {
                $(".dev-bulk-action-select").select2('val', '');
            });
        } else if (targetStatus === 'Unfavorite') {
            showConfirmationBox(listMessages.unfavoriteManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), bulkFunction, listMessages.unfavoriteManyModalTitle, function () {
                $(".dev-bulk-action-select").select2('val', '');
            });
        }
    }

    this.showAddAsContactModal = function () {
        var basicModal = new BasicModal();
        basicModal.show(addAsContactModalUrl, function () {
            $('.modal-body select.select2').select2();
            $('.dev-add-as-contact-confirm-button').click(function () {
                addToContactsFormData = $('#dev-add-as-contact-form').serializeArray();
                thisObject.confirmAddAsContactBulk(basicModal);
            });
        }, {});
        basicModal.onHide(function () {
            $('.dev-bulk-action-select').select2('val', '');
        });
    };

    this.confirmAddAsContactBulk = function (basicModal) {
        var selectedItemsNumber = $('tbody .dev-list-check:checked').length;
        showConfirmationBox(listMessages.addAsContactManyConfirmMessage.replace('%s', selectedItemsNumber).replace('%itemCountLabel', itemCountLabel), function() {
            showLoader();
            bulkFunction();
            basicModal.hide();
        }, listMessages.addAsContactManyModalTitle);
    };

    /* Binding events */
    var thisObject = this;

    $('#leftSide').on('click', '.dev-delete-action', function () {
        thisObject.confirmDeleteOne($(this));
    });

    $('#leftSide').on('click', '.dev-poll-delete-action', function () {
        thisObject.confirmDeleteOneAutoPublished($(this));
    });

    $('#leftSide').on('click', '.dev-event-delete-action', function () {
        thisObject.confirmDeleteOneAutoPublished($(this));
    });

    $('#leftSide').on('click', '.status', function () {
        thisObject.confirmChangeActiveStatus($(this));
    });

    $('#leftSide').on('click', '.dev-favourite', function () {
        thisObject.confirmChangeFavoriteStatus($(this));
    });

    $('#leftSide').on('click', '.dev-stop-resume', function () {
        thisObject.confirmChangeStopAnswer($(this));
    });

    $('#leftSide').on('click', '.publish', function () {
        thisObject.confirmChangePublish($(this));
    });

    $('#leftSide').on('click', '.dev-publish-event', function () {
        thisObject.confirmPublishEvent($(this));
    });
    $('#leftSide').on('click', '.dev-resendMail', function () {
        thisObject.confirmResendMail($(this));
    });

    $('body').on('click', '.dev-autopublish-event', function () {
        thisObject.viewAutoPublishModal($(this));
    });

    $('#leftSide').on('click', '.dev-move-to-album', function () {
        thisObject.showMoveModal($(this));
    });

    $('#leftSide').on('change', '.dev-bulk-action-select', function () {
        if ($(this).val()) {
            switch ($(this).val()) {
                case 'Add as contact':
                    thisObject.showAddAsContactModal();
                    break;
                case 'Delete':
                    thisObject.confirmDeleteBulk('Delete');
                    break;
                case 'Activate':
                    thisObject.confirmChangeActiveStatusBulk('Activate');
                    break;
                case 'Deactivate':
                    thisObject.confirmChangeActiveStatusBulk('Deactivate');
                    break;
                case 'Publish':
                    thisObject.confirmChangePublishBulk('Publish');
                    break;
                case 'Unpublish':
                    thisObject.confirmChangePublishBulk('Unpublish');
                    break;
                case 'Forward':
                    thisObject.showForwardModal();
                    break;
                case 'Move':
                    thisObject.showMoveModal();
                    break;
                case 'Export':
                    exportToExcel($('.dev-bulk-actions-form').serialize());
                    $(".dev-bulk-action-select").select2('val', '');
                    $('.dev-bulk-actions-form input[type="checkbox"]').iCheck('uncheck');
                    break;
                case 'Favorite':
                    thisObject.confirmChangeFavoriteStatusBulk('Favorite');
                    break;
                case 'Unfavorite':
                    thisObject.confirmChangeFavoriteStatusBulk('Unfavorite');
                    break;
            }
        }
    });

    $('#leftSide').on('click', '.dev-move-images', function () {
        thisObject.showMoveModal();
    });

    $('#leftSide').on('click', '.dev-change-columns', function () {
        thisObject.showColumnOptionsModal();
    });





}

var resendMailToVisitor=true;

function RoomList(roomName, roomMessages, calledFrom) {
    BaseList.call(this);

    this.confirmBackwardMessageAction = function (clickedElement) {
        backwardOneParams = {id: clickedElement.attr("data-id")};
        inputName = clickedElement.attr('data-name');
        showConfirmationBox(listMessages.backwardOneConfirmMessage.replace('%s', inputName), function backwardOne() {
            showLoader();
            var numOfRecords = $('tr[data-id]').length;
            var pageNum = getQueryVariable('page');
            $.ajax({
                url: backwardUrl,
                method: 'post',
                data: backwardOneParams,
                success: function (data) {
                    closeDialog();
                    hideLoader();
                    switch (data.status) {
                        case 'success':
                        case 'failed-reload':
                            if (pageNum !== 1 && numOfRecords === 1) {
                                retunToPreviousPage(pageNum);
                            } else {
                                stateChangeHandler();
                            }
                            break;
                        case 'failed':
                            if ($('#leftSide').find('.alert-danger').length > 0) {
                                $('#leftSide').find('.alert-danger').remove();
                            }
                            $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                            break;
                        case 'failedAlert':
                            showAlertBox(data.message);
                            break;
                    }
                }
            });
        }
        , listMessages.backwardOneModalTitle.replace('%s', inputName), hideLoader);
    }


    this.showForwardModal = function (clickedElementIfNotBulk, backwardFlag) {
        var basicModal = new BasicModal();
        var currentActionUrl = '';
        var params = {};
        if (!backwardFlag) {
            currentActionUrl = forwardToUrl;
        }
        else {
            currentActionUrl = backwardUrl;
            params['id'] = clickedElementIfNotBulk.attr("data-id");
        }
        basicModal.show(currentActionUrl, function () {
            $(".modal-body select").select2();

            $('#dev-rooms-select').on("select2-close", function () {
                $('#basicModal').attr('tabindex', '-1');
            }).on("select2-open", function () {
                $('#basicModal').removeAttr('tabindex');
            });
            $('#dev-rooms-select').change(function () {
//                $(".form-group.has-error label.error").html('');
//                $(".form-group.has-error").removeClass('has-error');
                thisObject.refreshRoomUsersSelect($(this), params);
            });

            $('.dev-forward-button').click(function () {
                if ($('#dev-forward-form').valid()) {
                    if (clickedElementIfNotBulk) {
                        thisObject.forwardMaterial(clickedElementIfNotBulk, basicModal, backwardFlag);
                    } else {
                        var selectedItemsNumber = $('tbody .dev-list-check:checked').length;
                        var forwardAttrs = {
                            roomFrom: roomName,
                            room: $('#dev-rooms-select').val(),
                            user: $('#dev-room-users-select').val(),
                            reason: $('#dev-forward-reason').val()
                        };
                        var confirmationMessage = "";
                        if ($('#dev-room-users-select').val() === "0") {
                            if($('.dev-forward-action').attr('data-status') == 'autopublish'){
                                confirmationMessage = roomMessages['forwardMultiConfirmMessageAutoPublish'];
                            }
                            else{
                                confirmationMessage = roomMessages['forwardMultiConfirmMessage'];
                            }
                        } else {
                            if($('.dev-forward-action').attr('data-status') == 'autopublish'){
                                confirmationMessage = roomMessages['forwardMultiConfirmMessageToUserAutoPublish'].replace('%username%', $('#dev-room-users-select option:selected').text());
                            }
                            else{
                                confirmationMessage = roomMessages['forwardMultiConfirmMessageToUser'].replace('%username%', $('#dev-room-users-select option:selected').text());
                            }
                        }
                        showConfirmationBox(confirmationMessage.replace('%number%', selectedItemsNumber).replace('%roomName%', $('#dev-rooms-select option:selected').text()),
                                function () {
                                    bulkFunction("", forwardAttrs, basicModal);
                                }, roomMessages['forwardConfirmTitle']);
                    }
                }
//                else {
//                    $('.error').addClass('help-block');
//                    $('.error').parent().parent('.form-group').addClass('has-error');
//                }
            });
        }, params);
        basicModal.onHide(function () {
            $(".dev-bulk-action-select").select2('val', '');
        });
    }



    this.showDeleteModal = function (clickedElementIfNotBulk) {
        var basicModal = new BasicModal();
        basicModal.show(deleteUrl, function () {
            $(".modal-body select").select2();

            $(".dev-delete-button").click(function () {
                if ($('#dev-delete-select').valid()) {
                    if (clickedElementIfNotBulk) {
                        thisObject.deleteMaterial(clickedElementIfNotBulk, basicModal);
                    } else {
                        var selectedItemsNumber = $('tbody .dev-list-check:checked').length;
                        var deleteAttrs = {
                            reason: $('#dev-delete-select').val()
                        };

                        if ($('#dev-delete-reason').val().trim() != "") {
                            deleteAttrs.otherReason = $('#dev-delete-reason').val();
                        }

                        var confirmationMessage = "";
                        if (typeof clickedElementIfNotBulk == "undefined") {
                            if($('.dev-material-delete-action').attr('data-status')){
                                if($('.dev-material-delete-action').attr('data-status')  =='new'){

                                    confirmationMessage = roomMessages['deleteMultiConfirmMessage'];
                                }else{
                                    confirmationMessage = roomMessages['deleteMultiConfirmMessageAutoPublish'];
                                }
                            }
                            else{
                                confirmationMessage = roomMessages['deleteMultiConfirmMessage'];
                            }
                        }

                        showConfirmationBox(confirmationMessage.replace('%number%', selectedItemsNumber),
                                function () {
                                    bulkFunction("", deleteAttrs, basicModal);
                                }, roomMessages['deleteConfirmTitle']);
                    }
                } else {
                    $('.error').addClass('help-block');
                    $('.error').parent().parent('.form-group').addClass('has-error');
                }
            });
        });
        basicModal.onHide(function () {
            $(".dev-bulk-action-select").select2('val', '');
        });
    }


    this.showPublishModal = function (clickedElement) {
        var basicModal = new BasicModal();
        basicModal.show(publishUrl, function () {
            $('.dev-publish-material-button').click(function () {
                var publishAction = clickedElement.attr("data-action");
                if (publishAction === 'autoPublish' && publishAction === 'autoPublishControl') {
                    setDateTime();
                    if (!$('#dev-publish-form').valid()) {
                        return;
                    }
                }
                thisObject.publishMaterial(publishUrl, clickedElement, basicModal);

//                showConfirmationBox(roomMessages['publishConfirmMessage'].replace('%title%', clickedElement.attr('data-name')).replace('%type%', clickedElement.attr('data-type')),
//                function(){publishMaterial(publishUrl, clickedElement, basicModal)}, roomMessages['forwardConfirmTitle']);
            });
        }, {'id': clickedElement.attr("data-id"), 'action': clickedElement.attr("data-action")});
    }

    this.showAutoPublishModal = function (clickedElement) {
        var basicModal = new BasicModal();
        basicModal.show(autopublishUrl, function () {
            $('.dev-publish-material-button').click(function () {
                var publishAction = clickedElement.attr("data-action");
                if (publishAction === 'autoPublish' && publishAction === 'autoPublishControl') {
                    setDateTime();
                    if (!$('#dev-publish-form').valid()) {
                        return;
                    }
                }
                thisObject.autopublishMaterial(autopublishUrl, clickedElement, basicModal);

//                showConfirmationBox(roomMessages['publishConfirmMessage'].replace('%title%', clickedElement.attr('data-name')).replace('%type%', clickedElement.attr('data-type')),
//                function(){publishMaterial(publishUrl, clickedElement, basicModal)}, roomMessages['forwardConfirmTitle']);
            });
        }, {'id': clickedElement.attr("data-id"), 'action': clickedElement.attr("data-action")});
    }

    this.autopublishMaterial = function (autopublishUrl, clickedElement, basicModal) {
        if (!$('#dev-publish-form').valid()) {
            return;
        }

        showConfirmationBox((clickedElement.hasClass('dev-autopublish-poll') && clickedElement.attr("data-action") === 'autoPublishControl')?roomMessages.doYouReallyWantToSave:listMessages.publishOneConfirmMessage.replace('%s', clickedElement.attr('data-name')).replace('%one', oneItem),
                function () {
                    showLoader();
                    postData = {
                        pollId: clickedElement.attr('data-id'),
                        autoPublishDate: $('#material_publish_time').val(),
                        action: clickedElement.attr('data-action')
                    };
                    $.ajax({
                        url: autopublishUrl,
                        method: 'POST',
                        data: postData,
                        success: function (data) {
                            if (data.status == "error") {
                                hideLoader();
                                $('#dev-publish-error-message').html(data.message);
                                $('#dev-publish-error-message').show();
                            } else {
                                basicModal.hide();
                                var numOfRecords = $('tr[data-id]').length;
                                var pageNum = getQueryVariable('page');
                                if (calledFrom === "list") {
                                    if (pageNum !== 1 && numOfRecords === 1) {
                                        retunToPreviousPage(pageNum);
                                    } else {
                                        stateChangeHandler();
                                    }
                                } else if (calledFrom === "view") {
                                    if (clickedElement.attr('data-status') == 'autopublish') {
                                        window.location = autopublishLink;
                                    } else {
                                        window.location = roomListUrl;
                                    }

                                }
                            }
                        }
                    });
                },listMessages.publishOneModalTitle);
    }

    this.publishMaterial = function (publishUrl, clickedElement, basicModal) {
        if (!$('#dev-publish-form').valid()) {
            return;
        }
        var message = roomMessages['publishConfirmMessage'];
        if (clickedElement.attr('data-status') == 'autopublish') {
            message = roomMessages['autopublishConfirmMessage'];
        }else if (clickedElement.attr('data-status') == 'autopublishControl') {
            message = roomMessages['doYouReallyWantToSave'];
        }
        showConfirmationBox(message.replace('%title%', clickedElement.attr('data-name')).replace('%type%', typeof definedStudioType != "undefined"?definedStudioType:clickedElement.attr('data-type')).replace('%gender%', clickedElement.attr('data-gender')),
                function () {
                    showLoader();
                    var publishChecks = $('#dev-publish-form input[type=checkbox]:checked');
                    var publishLocations = [];
                    if (publishChecks.length > 0) {
                        publishChecks.each(function (e, v) {
                            publishLocations[e] = $(v).val();
                        });
                    }
                    postData = {
                        sendPushNotification: $('#sendPushNotification:checked').val(),
                        locations: publishLocations,
                        materialId: clickedElement.attr('data-id'),
                        autoPublishDate: $('#material_publish_time').val(),
                        breakingNewColor: $('#breakingNewColor').val(),
                        action: clickedElement.attr('data-action')
                    };
                    $.ajax({
                        url: publishUrl,
                        method: 'POST',
                        data: postData,
                        success: function (data) {
                            if (data.status == "error") {
                                hideLoader();
                                $('#dev-publish-error-message').html(data.message);
                                $('#dev-publish-error-message').show();
                            } else {
                                basicModal.hide();
                                var numOfRecords = $('tr[data-id]').length;
                                var pageNum = getQueryVariable('page');
                                if (calledFrom === "list") {
                                    if (pageNum !== 1 && numOfRecords === 1) {
                                        retunToPreviousPage(pageNum);
                                    } else {
                                        stateChangeHandler();
                                    }
                                } else if (calledFrom === "view") {
                                    if (clickedElement.attr('data-status') == 'autopublish') {
                                        window.location = autopublishLink;
                                    } else {
                                        window.location = roomListUrl;
                                    }

                                }
                            }
                        }
                    });
                }, roomMessages['forwardConfirmTitle']);
    }

    this.forwardMaterial = function (clickedElement, basicModal, backwardFlag) {
        var materialId = clickedElement.attr("data-id");
        var materialTitle = clickedElement.attr("data-title");
        var currentActionUrl = '';
        var params = {};
        if (!backwardFlag) {
            currentActionUrl = forwardToUrl;
        }
        else {
            currentActionUrl = backwardUrl;
            params['id'] = materialId;
        }
        if ($('#dev-room-users-select').val() === "0") {
            if (!backwardFlag) {
                if(clickedElement.attr('data-status') == 'autopublish'){
                    confirmationMessage = roomMessages['forwardConfirmMessageAutoPublish'];
                }
                else{
                    confirmationMessage = roomMessages['forwardConfirmMessage'];
                }
            }
            else {
                confirmationMessage = roomMessages['backwardConfirmMessage'];
            }
        } else {
            if (!backwardFlag) {
                if(clickedElement.attr('data-status') == 'autopublish'){
                    confirmationMessage = roomMessages['forwardConfirmMessageToUserAutoPublish'].replace('%username%', $('#dev-room-users-select option:selected').text());
                }
                else{
                    confirmationMessage = roomMessages['forwardConfirmMessageToUser'].replace('%username%', $('#dev-room-users-select option:selected').text());
                }
            }
            else {
                confirmationMessage = roomMessages['backwardConfirmMessageToUser'].replace('%username%', $('#dev-room-users-select option:selected').text());
            }
        }
        showConfirmationBox(confirmationMessage.replace('%title%', materialTitle).replace('%roomName%', $('#dev-rooms-select option:selected').text()), function () {
            if ($('#dev-forward-form').valid()) {
                postData = {
                    roomFrom: roomName,
                    room: $('#dev-rooms-select').val(),
                    user: $('#dev-room-users-select').val(),
                    reason: $('#dev-forward-reason').val(),
                    id: materialId
                }
                var numOfRecords = $('tr[data-id]').length;
                var pageNum = getQueryVariable('page');
                $.ajax({
                    url: currentActionUrl,
                    method: 'POST',
                    data: postData,
                    success: function (data) {
                        if (data.status !== "success") {
                            $('#dev-forward-error-message').html(data.message);
                            $('#dev-forward-error-message').show();
                            if (data.status === "error-refresh-users") {
                                thisObject.refreshRoomUsersSelect($('#dev-rooms-select'), params);
                            }
                        } else {
                            basicModal.hide();
                            if (calledFrom === "list") {
                                if (pageNum !== 1 && numOfRecords === 1) {
                                    retunToPreviousPage(pageNum);
                                } else {
                                    stateChangeHandler();
                                }
                            } else if (calledFrom === "view") {
                                window.location = roomListUrl;
                            }
                        }
                    }
                });
            }
        }, roomMessages['forwardConfirmTitle']);
    }



    this.deleteMaterial = function (clickedElement, basicModal) {
        var materialId = clickedElement.attr("data-id");
        var materialTitle = clickedElement.attr("data-title");
        if(clickedElement.attr("data-status") == 'autopublish'){
            confirmationMessage = roomMessages['deleteConfirmMessageAutoPublish'];

        }
        else{
            confirmationMessage = roomMessages['deleteConfirmMessage'];
        }

        showConfirmationBox(confirmationMessage.replace('%title%', materialTitle), function () {
            if ($('#dev-delete-select').valid()) {
                postData = {
                    roomFrom: roomName,
                    reason: $('#dev-delete-select').val(),
                    id: materialId
                }

                if ($('#dev-delete-reason').val().trim() != "") {
                    postData.otherReason = $('#dev-delete-reason').val();
                }

                var numOfRecords = $('tr[data-id]').length;
                var pageNum = getQueryVariable('page');
                $.ajax({
                    url: deleteUrl,
                    method: 'POST',
                    data: postData,
                    success: function (data) {
                        if (data.status !== "success") {
                            if (typeof data.materialUpdate != 'undefined') {
                                showConfirmationSecondBox(roomMessages['deleteConfirmMessagematerialUpdate'], function () {
                                    $.ajax({
                                        url: deleteMaterialWithUpdate,
                                        method: 'POST',
                                        data: postData,
                                        success: function (data) {
                                            if (data.status == "success") {
                                                basicModal.hide();
                                                if (calledFrom === "list") {
                                                    if (pageNum !== 1 && numOfRecords === 1) {
                                                        retunToPreviousPage(pageNum);
                                                    } else {
                                                        refreshList();
                                                    }
                                                } else if (calledFrom === "view") {
                                                    window.location = roomListUrl;
                                                }
                                            }
                                        }});

                                });
                            } else {
                                $('#dev-delete-error-message').html(data.message);
                                $('#dev-delete-error-message').show();

                                if (data.status === "error-refresh-users") {
                                    thisObject.refreshRoomUsersSelect($('#dev-rooms-select'));
                                }
                            }
                        } else {
                            basicModal.hide();
                            if (calledFrom === "list") {
                                if (pageNum !== 1 && numOfRecords === 1) {
                                    retunToPreviousPage(pageNum);
                                } else {
                                    refreshList();
                                }
                            } else if (calledFrom === "view") {
                                window.location = roomListUrl;
                            }
                        }
                    }
                });
            }
        }, roomMessages['forwardConfirmTitle']);
    }
function showConfirmationSecondBox(confimationMessage, onConfirmFunction, confirmationBoxTitle, onCancelFunction) {
    var $confirmationModal = $('#secondConfirmationModal');
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
    this.refreshRoomUsersSelect = function (elem, params) {
        if (elem.val() !== 'chiefeditor') {
            params['roomId'] = elem.val();
            $.ajax({
                url: getRoomUsersUrl,
                method: 'POST',
                data: params,
                success: function (data) {
                    $('#dev-room-users-select').select2("val", "0");
                    if (data.users.length === 0) {
                        $('#dev-room-users-select option:not(".dev-default")').remove();
                        $('#dev-room-users-select').select2("enable", false);
                    } else {
                        $('#dev-room-users-select option:not(".dev-default")').remove();
                        for (id in data.users) {
                            var user = data.users[id]['name'];
                            //var onlineStatus = (data.users[id]['status']) ? 'online' : 'offline';
                            $('#dev-room-users-select').append($('<option>', {value: id, text: user}));
                        }
                        $('#dev-room-users-select option.offline').attr("disabled", "disabled");
                        $('#dev-room-users-select').select2("enable", true);
                        $('#dev-room-users-select').select2('val', data.defaultUserId);
                    }
                }
            });
        } else {
            $('#dev-room-users-select option:not(".dev-default")').remove();
            $('#dev-room-users-select').select2();
            $('#dev-room-users-select').select2("enable", false);
        }
    }

    this.assignToMe = function (clickedElement) {
        var Params = {materialId: clickedElement.attr("data-id"), type: clickedElement.attr("data-type")};
        if (assign) {
            assign = false;
            $.ajax({
                url: clickedElement.attr("data-url"),
                data: Params,
                method: 'post',
                success: function (data) {
                    if (data.type == 'list') {
                        assign = true;
                        switch (data.status) {
                            case 'success':
                            case 'failed':
                                refreshList();
                                break;
//                        case 'failed':
////                            $('.remove-5s').remove();
////                            $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
//                            refreshList()
//                            break;
                            case 'failedAlert':
                                showAlertBox(data.message, null, refreshList);
                                break;
                        }
                    } else {
                        switch (data.status) {
                            case 'success':
                            case 'failed':
                                window.location.reload();
                                break;
//                        case 'failed':
//                            $('.remove-5s').remove();
//                            $('#viewLeftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
//                            break;
                            case 'failedAlert':
                                showAlertBox(data.message, null, removeButtons);
                                break;
                        }
                    }
                }

            });
        }

    }



    this.confirmUnpublishOne = function (clickedElement) {
        showConfirmationBox(roomMessages['unpublishFromLocationsOneConfirmMessage'].replace('#title#', clickedElement.attr('data-name')).replace('#type#', clickedElement.attr('data-type')).replace('#gender#', clickedElement.attr('data-gender')), function () {
            unpublish(clickedElement.attr('data-id'));
        }, roomMessages['unpublishFromLocationsOneModalTitle'].replace('#type#', clickedElement.attr('data-type')), hideLoader);
    };

    /* Binding events */
    var thisObject = this;

    $('body').on('click', '.dev-forward-action', function () {
        thisObject.showForwardModal($(this), false);
    });
    $('body').on('click', '.dev-backward-action', function () {
        thisObject.showForwardModal($(this), true);
    });
    $('body').on('click', '.dev-backward-message-action', function () {
        thisObject.confirmBackwardMessageAction($(this), true);
    });
    $('body').on('click', '.dev-publish-action', function () {
        thisObject.showPublishModal($(this));
    });

    $('body').on('click', '.dev-autopublish-poll', function () {
        thisObject.showAutoPublishModal($(this));
    });


    $('body').on('click', '.dev-material-delete-action', function () {
        thisObject.showDeleteModal($(this));
    });

    $('body').on('click', '.dev-unpublish-action', function () {
        thisObject.confirmUnpublishOne($(this));
    });

    $('body').on('click', '.dev-assign-to-me', function (e) {
        e.preventDefault();
        thisObject.assignToMe($(this));
    });

    $('#secondConfirmationModal .dev-confirm').click(function () {
        $('#secondConfirmationModal').modal('hide');
        callbackFunction();
    });
}

$('body').on('click', '.dev-forceRassign-action', function (e) {
    e.preventDefault();
    forceReassignModal($(this));
});

function forceReassignModal(clickedElement) {
    var basicModal = new BasicModal();
    var params = {};
    params['id'] = clickedElement.attr("data-id");
    params['roomName'] = clickedElement.attr("data-room");
    basicModal.show(forceReassignUrl, function () {
        $(".modal-body select").select2();

        $('.dev-forceReassign-button').click(function () {
            if ($('#dev-forceReassign-form').valid()) {
                forceReassignMaterial(clickedElement, basicModal);
            }
        });
    }, params);
}

function forceReassignMaterial(clickedElement, basicModal) {
    var params = {};
    confirmationMessage = roomMessages['forceReassignConfirmMessageToUser'].replace('%title%', clickedElement.attr("data-title"));
    showConfirmationBox(confirmationMessage, function () {
        if ($('#dev-forceReassign-form').valid()) {
            postData = {
                user: $('#dev-room-users-select').val(),
                id: clickedElement.attr("data-id"),
                currentRoom: clickedElement.attr("data-room"),
            }
            $.ajax({
                url: forceReassignUrl,
                method: 'POST',
                data: postData,
                success: function (data) {
                    if (data.status !== "success") {
                        $('#dev-forceReassign-error-message').html(data.message);
                        $('#dev-forceReassign-error-message').show();
                        if (data.status === "error-refresh-users") {
                            refreshReassignUserSelect(clickedElement.attr("data-id"), clickedElement.attr("data-room"));
                        }
                    } else {
                        basicModal.hide();
                        if (window.location.href.indexOf("view") == -1) {
                            stateChangeHandler();
                        } else {
                            location.reload();
                        }
                    }
                }
            });
        }
    }, roomMessages['forwardConfirmTitle']);
}

function refreshReassignUserSelect(id, roomId) {
    $.ajax({
        url: getRoomUsersUrl,
        method: 'POST',
        data: {id: id, roomId: roomId, forceReassign: true},
        success: function (data) {
            $('#dev-room-users-select').select2("val", "").trigger('change');
            if (data.users.length === 0) {
                $('#dev-room-users-select option:not(".dev-default")').remove();
            } else {
                $('#dev-room-users-select option:not(".dev-default")').remove();
                for (id in data.users) {
                    var user = data.users[id]['name'];
                    //var onlineStatus = (data.users[id]['status']) ? 'online' : 'offline';
                    $('#dev-room-users-select').append($('<option>', {value: id, text: user}));
                }
                $('#dev-room-users-select option.offline').attr("disabled", "disabled");
            }
        }
    });
}
$('body').on('click', '.dev-assignTo-action', function (e) {
    e.preventDefault();
    assignToModal($(this));
});

function assignToModal(clickedElement) {
    var basicModal = new BasicModal();
    var params = {};
    params['id'] = clickedElement.attr("data-id");
    basicModal.show(clickedElement.attr("data-url"), function () {
        $(".modal-body select").select2();

        $('.dev-assignTo-button').click(function () {
            $('#dev-room-users-select').parents('.form-group').removeClass('has-error');

            if ($('#dev-assignTo-form').valid()) {
                assignToMessage(clickedElement, basicModal);
            } else {
                $('.error[for="dev-room-users-select"]').addClass('help-block');
                $('#dev-room-users-select').parents('.form-group').addClass('has-error');
            }
        });
    }, params);
}

function assignToMessage(clickedElement, basicModal) {
    var params = {};
    confirmationMessage = roomMessages['forwardToConfirmMessage'].replace('%title%', clickedElement.attr("data-title"));
    showConfirmationBox(confirmationMessage, function () {
        if ($('#dev-assignTo-form').valid()) {
            postData = {
                user: $('#dev-room-users-select').val(),
                id: clickedElement.attr("data-id")
            }
            $.ajax({
                url: clickedElement.attr("data-url"),
                method: 'POST',
                data: postData,
                success: function (data) {
                    if (data.status !== "success") {
                        $('#dev-assignTo-error-message').html(data.message);
                        $('#dev-assignTo-error-message').show();
//                        if (data.status === "error-refresh-users") {
//                            refreshReassignUserSelect(clickedElement.attr("data-id"), clickedElement.attr("data-room"));
//                        }
                    } else {
                        basicModal.hide();
                        if (window.location.href.indexOf("view") == -1) {
                            stateChangeHandler();
                        } else {
                            window.location = roomListUrl;
                        }
                    }
                }
            });
        }
    }, roomMessages['forwardToConfirmTitle']);
}

function refreshReassignUserSelect(id, roomId) {
    $.ajax({
        url: getRoomUsersUrl,
        method: 'POST',
        data: {id: id, roomId: roomId, forceReassign: true},
        success: function (data) {
            $('#dev-room-users-select').select2("val", "").trigger('change');
            if (data.users.length === 0) {
                $('#dev-room-users-select option:not(".dev-default")').remove();
            } else {
                $('#dev-room-users-select option:not(".dev-default")').remove();
                for (id in data.users) {
                    var user = data.users[id]['name'];
                    //var onlineStatus = (data.users[id]['status']) ? 'online' : 'offline';
                    $('#dev-room-users-select').append($('<option>', {value: id, text: user}));
                }
                $('#dev-room-users-select option.offline').attr("disabled", "disabled");
            }
        }
    });
}
function removeButtons() {
    $('.dev-assign-to-me').remove();
    $('.clock').remove();
}
function deleteOne() {
    showLoader();
    var numOfRecords = $('tr[data-id]').length;
    var pageNum = getQueryVariable('page');
    $.ajax({
        url: deleteUrl,
        method: 'post',
        data: deleteOneParams,
        success: function (data) {
            closeDialog();
            hideLoader();
            switch (data.status) {
                case 'success':
                case 'failed-reload':
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    break;
                case 'failed':
                    if ($('#leftSide').find('.alert-danger').length > 0) {
                        $('#leftSide').find('.alert-danger').remove();
                    }
                    $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                    break;
                case 'failedAlert':
                    showAlertBox(data.message);
                    break;
            }
        }
    });
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {string} id
 */
function unpublish(id) {
    showLoader();
    $.ajax({
        url: unPublishUrl,
        method: 'post',
        data: {id: id},
        success: function (data) {
            closeDialog();
            hideLoader();
            switch (data.status) {
                case 'success':
                case 'failed-reload':
                    var numOfRecords = $('tr[data-id]').length;
                    var pageNum = getQueryVariable('page');
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    break;
                case 'failed':
                    if ($('#leftSide').find('.alert-danger').length > 0) {
                        $('#leftSide').find('.alert-danger').remove();
                    }
                    $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                    scrollToFirstNotification();
                    break;
                case 'failedAlert':
                    showAlertBox(data.message);
            }
        }
    });
}

function toggleActiveStatus() {
    showLoader();
    var numOfRecords = $('tr[data-id]').length;
    var pageNum = getQueryVariable('page');
    $.ajax({
        url: changeStatusUrl,
        data: changeStatusParams,
        success: function (data) {
            closeDialog();
            hideLoader();
            switch (data.status) {
                case 'success':
                case 'failed-reload':
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    break;
                case 'failed':
                    $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                    break;
                case 'failedAlert':
                    showAlertBox(data.message, null, stateChangeHandler);
                    break;
            }
        }
    });
}

function toggleFavoriteStatus() {
    showLoader();
    var numOfRecords = $('tr[data-id]').length;
    var pageNum = getQueryVariable('page');
    $.ajax({
        url: changeStatusFavoriteUrl,
        data: changeStatusFavoriteParams,
        success: function (data) {
            closeDialog();
            hideLoader();
            switch (data.status) {
                case 'success':
                case 'failed-reload':
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    break;
                case 'failed':
                    $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                    break;
                case 'failedAlert':
                    showAlertBox(data.message, null, stateChangeHandler);
                    break;
            }
        }
    });
}

function togglePublish() {
    showLoader();
    var numOfRecords = $('tr[data-id]').length;
    var pageNum = getQueryVariable('page');
    $.ajax({
        url: changePublishUrl,
        data: changePublishParams,
        success: function (data) {
            closeDialog();
            hideLoader();
            switch (data.status) {
                case 'success':
                case 'failed-reload':
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    break;
                case 'failed':
                    $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                    break;
                case 'failedAlert':
                    showAlertBox(data.message, null, stateChangeHandler);
                    break;
            }
        }
    });
}

function resendMail() {
        if (resendMailToVisitor) {
            resendMailToVisitor = false;
            $.ajax({
                url: resendParameterUrl,
                data: resendParameter,
                method: 'post',
                success: function (data) {
                        resendMailToVisitor = true;
                        switch (data.status) {
                            case 'success':
                            case 'failed':
                                refreshList();
                                break;
//                        case 'failed':
////                            $('.remove-5s').remove();
////                            $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
//                            refreshList()
//                            break;
                            case 'failedAlert':
                                showAlertBox(data.message, null, refreshList);
                                break;
                        }

                }

            });
        }

    }

function publishEvent() {
    showLoader();
    var numOfRecords = $('tr[data-id]').length;
    var pageNum = getQueryVariable('page');
    $.ajax({
        url: publishUrl,
        data: publishParams,
        success: function (data) {
            closeDialog();
            hideLoader();
            if (calledFrom === "view") {
                window.location = roomListUrl;
                return;
            }
            switch (data.status) {
                case 'success':
                case 'failed-reload':
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    break;
                case 'failed':
                    $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                    refreshList();
                    break;
                case 'failedAlert':
                    showAlertBox(data.message, null, stateChangeHandler);
                    break;
            }
        }
    });
}

function bulkFunction(deleteAction, forwardAttrs, callingFromModal) {
    $('tr[data-id]').each(function () {
        $(this).removeClass('success').removeClass('danger').find('td:last').html('');
    });
    var $form = $('.dev-bulk-actions-form');
    var formData = $form.serializeArray();
    formData.push({name: 'deleteOption', value: deleteAction});
    for (key in forwardAttrs) {
        formData.push({name: key, value: forwardAttrs[key]})
    }
    for (var i = 0; i < addToContactsFormData.length; i++) {
        formData.push(addToContactsFormData[i]);
    }
    var numOfRecords = $('tr[data-id]').length;
    var numOfCheckedRecord = $('tbody .dev-list-check:checked').length;
    var pageNum = getQueryVariable('page');

    $.ajax({
        url: $form.attr('action'),
        data: formData,
        type: $form.attr('method'),
        complete: function () {
            closeDialog();
            hideLoader();
        },
        success: function (data) {
            if(data['bulk-action']== 'Activate' || data['bulk-action']=='Deactivate' || data['bulk-action']== 'Favorite' || data['bulk-action']=='Unfavorite' || data['bulk-action'] === 'Add as contact'){
            stateChangeHandler({
                async: false
            });
            }
            var refreshed=true;
            if (callingFromModal || data['bulk-action'] === 'Forward') {
                callingFromModal.hide();
            }
             if ($form.hasClass('dev-refresh-page') && $.inArray(data['bulk-action'], ['Activate', 'Deactivate']) == -1 && $("#isAlbumForm").val() != "true") {
                if (data['bulk-action'] === 'Delete' || data['bulk-action'] === 'Forward' || data['bulk-action'] === 'Move' && data.errors.length == 0) {
                    if ((data.success).length === numOfCheckedRecord && data.status === 'success' && pageNum !== 1 && numOfRecords === numOfCheckedRecord) {
                        retunToPreviousPage(pageNum);
                    } else if ((data.success).length === 0 && data.status === 'success' && pageNum !== 1 && numOfRecords === numOfCheckedRecord) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    refreshed=false;
                }
            }
            setTimeout(function () {
                if ($("#isAlbumForm").val() == "true") {
                    showNotification(messages.movedSuccessfuly);
                } else {
                    showAlertBox(data.message, "", function () {
                        if (refreshed) {
                            if (data['bulk-action'] === 'Delete' || data['bulk-action'] === 'Forward' || data['bulk-action'] === 'Move') {
                                if ((data.success).length === numOfCheckedRecord && data.status === 'success' && pageNum !== 1 && numOfRecords === numOfCheckedRecord) {
                                    retunToPreviousPage(pageNum);
                                } else if ((data.success).length === 0 && data.status === 'success' && pageNum !== 1 && numOfRecords === numOfCheckedRecord) {
                                    retunToPreviousPage(pageNum);
                                } else {
                                    stateChangeHandler();
                                }
                            }
                        }
                    });
                }
            }, 200);
            if (data.status === 'success') {
                for (message in data.errors) {
                    for (index in data.errors[message]) {
                        var $tr = $('tr[data-id="' + data.errors[message][index] + '"]');
                        if ($tr.length !== 0) {
                            $(".dev-list-table").removeClass("dev-hide-errors");
                            $tr.find('input[type="checkbox"]').iCheck('check');
                            $tr.addClass('danger').find('td:last').html(message);
                        }
                    }
                }
                showBulkActionSelect();
            }
        }
    });
    $(".dev-bulk-action-select").select2('val', '');
}

function saveListSelectedColumns(basicModal, url) {
    //modified to use this way instead of form serialize to fix this bug #3535:
    var str = "";
    $(".pickList_targetList .pickList_listItem").each(function () {
        str += "columns[]=" + $(this).attr("data-value") + "&";
    });

    $.ajax({
        url: url,
        method: 'POST',
        data: str,
        success: function () {
            basicModal.hide();
            stateChangeHandler();
        }
    });
}

function BasicModal() {
    var thisObject = this;
    this.hideCallback;
    this.show = function (url, callback, params) {
        $('#basicModal').on('hidden.bs.modal', function () {
            thisObject.hide();
        });
        $.ajax({
            url: url,
            method: 'GET',
            data: params,
            success: function (data) {
                if (data.status == 'failed-reload') {
                    thisObject.hide();
                    var numOfRecords = $('tr[data-id]').length;
                    var pageNum = getQueryVariable('page');
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    return;
                }
                if (data.status == "error") {
                    showAlertBox(data.message);
                    return;
                }
                var basicModal = $('#basicModal');
                basicModal.find('.modal-content').html(data);
                initFormValidation('#dev-forward-form');
                $('#dev-forward-form select').on('change', function () {
                    $(this).trigger('blur');
                });
                initFormValidation('#dev-forceReassign-form');
                $('#dev-forceReassign-form select').on('change', function () {
                    $(this).trigger('blur');
                });

                $('select.select2').on('select2-close', function () {
                    $('#basicModal').attr('tabindex', '-1');
                }).on("select2-open", function () {
                    $('#basicModal').removeAttr('tabindex');
                });
                callback();
                basicModal.modal({keyboard: true})
                basicModal.modal('show');
            }
        });
    }
    this.hide = function () {
        $('#basicModal .select2').select2('destroy');
        $('#basicModal').modal('hide');
        if (thisObject.hideCallback !== undefined)
            thisObject.hideCallback();
    }
    this.onHide = function (callback) {
        thisObject.hideCallback = callback;
    }
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
//    pageNumber--;
//    var url = window.location.pathname+window.location.search.replace(/page=(\d+)/, 'page='+pageNumber);
//    if(!detectIE()) {
//        pushNewState(null,null,url);
//        stateChangeHandler();
//    } else {
//        window.location.href = url;
//    }
    stateChangeHandler();
}

function closeDialog() {
    $('#choices-modal').modal('hide');
}


/**
 * ajax updating method
 * @author Gehad Mohamed <gehad.mohamed@ibtikar.net.sa>
 *
 * @param string url
 * @param function callback
 */

function ajaxUpdateTable(url, options, callback) {
    showLoader();
    var params = options.data ? options.data : {};
    $.ajax({
        url: url,
        async: typeof options.async !== "undefined" ? options.async : true,
        data: params,
//        dataType: "html",
        success: function (data) {

            hideLoader();
// fix for history api backbutton behavior in case of moving from one to another list
            if (data.indexOf('viewport') !== -1) {
                return;
            }
            // IE crash if you use replaceWith without empty before it
            $(options.elm).empty().replaceWith(data);

            currentPage = $('.dev-currentPaginationPage').val();
            if (currentPage !== '1' && GetURLPageParameter() !== currentPage) {
                currentPage = (currentPage < 1) ? 1 : currentPage;
                var newUrl = window.location.pathname + window.location.search.replace(/page=(\d+)/, 'page=' + currentPage);
                pushNewState(null, null, newUrl);
            }

            scrollToFirstNotification();
            $('body').trigger('listCallback');
            if (typeof callback !== "undefined") {
                callback();
            }
            fixChromeScrollIssue();

        }
    });
}
/**
 * fix chrome scroll issue
 */

/**
 * Get URL Page Parameter
 * @author Ahmad Gamal <a.gamal.net.sa>
 * @return page parameter value
 */
function GetURLPageParameter() {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] == 'page') {
            return sParameterName[1];
        }
    }
}


function fixChromeScrollIssue() {
    $('.table-responsive').scroll(function () {
        var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
        if (is_chrome) {
            if ($(this).scrollLeft() > ($(this).find("table").width() - $(this).width() + 4)) {
                $(this).scrollLeft(0);
            }
            else if ($(this).scrollLeft() < 0) {
                $(this).scrollLeft(($(this).find("table").width() - $(this).width()));
            }
        }
    });
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function showBulkActionSelect() {
    if ($('tbody .dev-list-check:checked').length > 0) {
        $('.dev-bulk-action').show();
    } else {
        $('.dev-bulk-action').hide();
    }
}

function stateChangeHandler(extraOptions) {
    var options = {elm: $('#bulk-form')};
    if (typeof extraOptions !== "undefined" && typeof extraOptions === "object") {
        options = $.extend(true, {}, options, extraOptions);
    }
    $('.remove-5s').slideUp();
    $('.remove-5s').remove();
    ajaxUpdateTable(window.location.pathname + window.location.search, options, function () {
        target_admin.init();
        initSelect2();
        $('.export-btn').attr('href', window.location.href.replace('/list', '/export'));

    });
}

// to refresh the current list
function refreshList() {
    if (!detectIE()) {
        stateChangeHandler();
    } else {
        window.location.reload();
    }
}

function exportToExcel(ids) {

    ids = (typeof ids !== "undefined" ? (window.location.search !== "" ? "&" + ids : "?" + ids) : "");

    var url = window.location.pathname.replace('/list', '/export') + window.location.search + ids;
    var iconElm = $('a.export-btn').find('.glyphicon');

    if (url.indexOf('staff') != -1) {
        var notifier = '<div class="alert alert-success remove-5s" style="margin:0;">'
                + listMessages.buildFileProgress
                + '</div>';
        var messageElm = $('a.export-btn').find('.download-msg');
//                messageElm.html(" "+listMessages.buildFileProgress);
        $('.dev-bulk-actions-form').prepend(notifier);
        iconElm.attr('class', 'fa fa-spinner fa-spin');



        $.fileDownload(url, {
            successCallback: function (url) {
//                        messageElm.html("");
                $('.dev-bulk-actions-form .alert-success').slideUp();
                iconElm.attr('class', 'glyphicon glyphicon-export');
            },
            failCallback: function (responseHtml, url) {
                window.location.reload();
//                        messageElm.html("");
                $('.dev-bulk-actions-form .alert-success').slideUp();
                iconElm.attr('class', 'glyphicon glyphicon-export');
            }
        });
    } else if(url.indexOf('visitor') != -1) {
        iconElm.attr('class', 'fa fa-spinner fa-spin');
        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                if (data.status === 'success') {
                    showAlertBox(data.message);
                    iconElm.attr('class', 'glyphicon glyphicon-export');
                }
            }
        });
    }else if(url.indexOf('contact') != -1  || url.indexOf('group') != -1 ) {

         iconElm.attr('class', 'fa fa-spinner fa-spin');
        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                if (data.status === 'success') {
                    showAlertBox(data.message);
                    iconElm.attr('class', 'glyphicon glyphicon-export');
                }
            }
        });

    }
}

/**
 * start the list timers count down
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 */
function startTimers() {
    $('.dev-list-timer').each(function () {
        var $this = $(this);
        var endTime = new Date(Math.round(new Date().getTime() + ($this.attr('data-timer-end') * 1000)));
        $this.countdown({
            date: endTime,
            render: function (time) {
                $this.html(getCountDownDisplayFormat(time));
            },
            onEnd: function () {
                refreshList();
            }
        });
    });
}

jQuery(document).ready(function ($) {
    startTimers();
    $('body').on('listCallback', function () {
        startTimers();
    });

    // export btn
    $('.export-btn').attr('href', window.location.href.replace('/list', '/export'));

    $(document).on("click", "a.export-btn", function () {
        exportToExcel();
        return false; //this is critical to stop the click event which will trigger a normal file download!
    });



    $('#leftSide').on('change', '.dev-list-check', showBulkActionSelect);
    showBulkActionSelect();

    $(window).on("popstate", function (e) {
        if (typeof stateChanged !== "undefined" && stateChanged) {
            stateChangeHandler();
        }
    });

    $('#leftSide').on('change', '#maxItemPerPage', function () {
        var url = window.location.pathname;
        if (window.location.search.indexOf('?') == -1) {
            url += "?limit=" + $('#maxItemPerPage').find(":selected").val();
        } else if (window.location.search.indexOf('limit') == -1) {
            url += window.location.search + "&limit=" + $('#maxItemPerPage').find(":selected").val();
        } else {
            url += window.location.search.replace(/limit=(\d+)/, "limit=" + $('#maxItemPerPage').find(":selected").val());
        }

        if (detectIE()) {
            window.location.href = url.replace(/&?page=\d+/, '');
        } else {
            ajaxUpdateTable(url.replace(/&?page=\d+/, ''), {elm: $('#bulk-form')}, function () {
                target_admin.init();
                initSelect2();
                $('.export-btn').attr('href', window.location.href.replace('/list', '/export'));

            });

            pushNewState(null, null, url.replace(/\&page=(\d+)/, ''));
        }
    });

    if (!detectIE()) {
        $('#leftSide').on('click', '[href^="' + document.location.pathname + '"]', function (e) {
            e.preventDefault();
            if ($(this).hasClass('dev-current-page')) {
                return;
            }
            ajaxUpdateTable($(this).attr('href'), {elm: $('#bulk-form')}, function () {

                target_admin.init();
                initSelect2();
                $('.export-btn').attr('href', window.location.href.replace('/list', '/export'));

            });
            pushNewState(null, null, $(this).attr('href'));
        });
    }

//    //fix for chrome overflow scroll when dragged out of the window
//    $('body,window,#leftSide,.container').on('mouseleave mouseout',function(){
//        $('.table-responsive').scrollLeft( $('.table-responsive').scrollLeft());
//    });


//            fix the validation border and text when change reason in delete material popup
    $('body').on('change', '#dev-delete-select', function () {

        $('#dev-delete-select').parents('.form-group').removeClass('has-error');

        if (!$(this).valid()) {
            $("#s2id_dev-delete-select .select2-choice").tooltip('destroy');
            $('.error[for="dev-delete-select"]').addClass('help-block');
            $('#dev-delete-select').parents('.form-group').addClass('has-error');
        } else {
            $("#s2id_dev-delete-select .select2-choice").tooltip('destroy').tooltip({
                title: $('#s2id_dev-delete-select .select2-chosen').text()
            });
        }

    });

    $(document).on('change', '#dev-room-users-select', function () {
        $('#dev-room-users-select').parents('.form-group').removeClass('has-error');
        $('#dev-room-users-select').siblings('.help-block').remove();
    });



});

function pushNewState(data, title, url) {
    stateChanged = true;
    history.pushState(data, title, url);
}
function isDefaultSearchForm(formSelctor) {
    var isDefault = true;
    parts = defaultSearchForm.split("&");
    for (i in parts) {
        params = parts[i].split("=");
        if (params[1] != 0 && params[1] != "") {
            isDefault = false;
            return;
        }
    }
    return isDefault;
}

function moveImageToAlbum() {
    showLoader();
    var numOfRecords = $('tr[data-id]').length;
    var pageNum = getQueryVariable('page');
    $.ajax({
        url: moveUrl,
        method: 'post',
        data: moveToAlbumParams,
        success: function (data) {
            closeDialog();
            hideLoader();
            switch (data.status) {
                case 'success':
                case 'failed-reload':
                    if (pageNum !== 1 && numOfRecords === 1) {
                        retunToPreviousPage(pageNum);
                    } else {
                        stateChangeHandler();
                    }
                    break;
                case 'failed':
                    if ($('#leftSide').find('.alert-danger').length > 0) {
                        $('#leftSide').find('.alert-danger').remove();
                    }
                    $('#leftSide').prepend('<div class="alert alert-danger remove-5s"> <a aria-hidden="true" href="#" data-dismiss="alert" class="close">×</a>' + data.message + '</div>');
                    break;
                case 'failedAlert':
                    showAlertBox(data.message);
                    break;
            }
        }
    });
}