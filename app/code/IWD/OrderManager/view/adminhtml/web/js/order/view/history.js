define([
        'jquery',
        'mage/translate',
        'IWD_OrderManager/js/order/view/actions',
        'jquery/ui'
    ],

function ($, $t, actions) {
    'use strict';

    $.widget('mage.iwdOrderManagerHistory', $.mage.iwdOrderManagerActions,{
        options:{
            urlDelete : '',
            urlUpdate : '',
            urlForm : '',

            actionBlock: '.note-list-actions-buttons',

            updateLinkClass: '.note-list-actions-update',
            updateBlock: '.note-list-actions-block-update',
            updateCancelButton: '.note-list-actions-block-update button.cancel',
            updateConfirmButton: '.note-list-actions-block-update button.update',

            deleteLinkClass: '.note-list-actions-delete',
            deleteBlock: '.note-list-actions-block-delete',
            deleteCancelButton: '.note-list-actions-block-delete button.cancel',
            deleteConfirmButton: '.note-list-actions-block-delete button.delete'
        },

        init: function(options) {
            this._initOptions(options);

            this.onClickAction(this.options.deleteLinkClass, 'deleteComment(commentId)');
            this.onClickAction(this.options.deleteCancelButton, 'deleteCommentCancel(commentId)');
            this.onClickAction(this.options.deleteConfirmButton, 'deleteCommentConfirm(commentId)');

            this.onClickAction(this.options.updateLinkClass, 'updateComment(commentId)');
            this.onClickAction(this.options.updateCancelButton, 'updateCommentCancel(commentId)');
            this.onClickAction(this.options.updateConfirmButton, 'updateCommentConfirm(commentId)');
        },

        onClickAction: function(actionClass, action) {
            var self = this;

            $(document).off('click touchstart', actionClass);
            $(document).on('click touchstart', actionClass, (function(e) {
                e.preventDefault();
                var commentId = $(this).data('comment-id');
                if(commentId) {
                    eval("self." + action);
                }
            }));
        },

        hideActionsBlock: function(id){
            $(this.options.actionBlock + '[data-comment-id=' + id + ']').css('display', 'none');
        },
        showActionsBlock: function(id){
            $(this.options.actionBlock + '[data-comment-id=' + id + ']').css('display', '');
        },

        isDisallowed:function(){
            var disallowed = this.options.disallowed.length > 0;
            if(disallowed){
                this.errorMessagePopup(this.options.disallowed[0]);
            }
            return disallowed;
        },

        /** DELETE **/
        deleteComment: function(id) {
            if(this.isDisallowed()){
                return;
            }

            this.hideActionsBlock(id);
            this.showDeleteBlock(id);
            this.scrollToTop(id, this.options.deleteBlock);
        },

        showDeleteBlock: function(id) {
            var block = $(this.options.deleteBlock + '[data-comment-id=' + id + ']');
            $(block).show();
            $(block).find('button').removeAttr('disabled').removeClass('disabled');
        },

        hideDeleteBlock: function(id) {
            $(this.options.deleteBlock + '[data-comment-id=' + id + ']').hide();
        },

        beforeDelete: function(id) {
            var block = $(this.options.deleteBlock + '[data-comment-id=' + id + ']');
            $(block).closest('li').find(".iwd-om-pre-loader").removeClass('hide');
            $(block).find('button').attr('disabled', 'disabled').addClass('disabled');
        },

        afterDeleteSuccess: function(id, response) {
            var block = $(this.options.deleteBlock + '[data-comment-id=' + id + ']');
            $(block).closest('li').find(".iwd-om-pre-loader").addClass('hide');

            if(!response.allowed){
                this.deleteCommentCancel(id);
                this.showActionsBlock(id);
                return this.showModal(response);
            }

            $(block).find('.iwd-om-message').text(response.result);
            setTimeout(function() {
                $(block).find('button').hide(1000);
                $(block).closest('li').find('.note-list-comment').hide(1000);
                setTimeout(function() {
                    $(block).closest('li').remove();
                }, 1000);
            }, 1000);
        },

        afterDeleteFail: function(id, error) {
            var block = $(this.options.deleteBlock + '[data-comment-id=' + id + ']');
            $(block).find('.iwd-om-message').text($t('Sorry, can not delete comment.'));
            $(block).closest('li').find(".iwd-om-pre-loader").addClass('hide');
            console.log(JSON.stringify(error));
        },

        deleteCommentCancel: function(id) {
            this.hideDeleteBlock(id);
            this.showActionsBlock(id);
            this.scrollToTop(id, this.options.actionBlock);
        },

        deleteCommentConfirm: function(id){
            var self = this;
            var data = {'id':id, 'form_key':FORM_KEY, 'order_id':self.getCurrentOrderId()};

            $.ajax({
                url: this.options.urlDelete,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
                beforeSend: function() {
                    self.beforeDelete(id);
                }
            })
            .done(function(response) {
                if (response.error || response.status == false){
                    self.afterDeleteFail(id, response);
                } else {
                    self.afterDeleteSuccess(id, response);
                }
            })
            .fail(function(error) {
                self.afterDeleteFail(id, error);
            });
        },

        scrollToTop:function(id, block){
            var top = $(block + '[data-comment-id=' + id + ']').offset().top - 80;
            top = top > 0 ? top : 0;
            $('html, body').animate({scrollTop:top}, 200);
        },


        /** UPDATE **/
        updateComment: function(id) {
            if(this.isDisallowed()){
                return;
            }

            this.hideActionsBlock(id);
            this.loadEditForm(id);
        },

        loadEditForm: function(id){
            var self = this;
            var data = {'id':id, 'form_key':FORM_KEY, 'order_id':self.getCurrentOrderId()};

            $.ajax({
                url: this.options.urlForm,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
                beforeSend: function() {
                    self.beforeLoadForm(id);
                }
            })
                .done(function(response) {
                    if (response.error || response.status == false){
                        self.afterLoadFormFail(id, response);
                    } else {
                        self.afterLoadFormSuccess(id, response);
                    }
                })
                .fail(function(error) {
                    self.afterLoadFormFail(id, error);
                });
        },

        beforeLoadForm: function(id){
            var block = $(this.options.updateBlock + '[data-comment-id=' + id + ']');
            $(block).find('button').attr('disabled', 'disabled').addClass('disabled');
            $(block).closest('li').find(".iwd-om-pre-loader").removeClass('hide');
        },

        afterLoadFormFail: function(id, error){
            var block = $(this.options.updateBlock + '[data-comment-id=' + id + ']');
            $(block).find('.iwd-om-message').text($t('Sorry, can not edit comment.')).addClass('hide');
            $(block).closest('li').find(".iwd-om-pre-loader").addClass('hide');
            console.log(JSON.stringify(error));
        },

        afterLoadFormSuccess: function(id, response){
            var block = $(this.options.updateBlock + '[data-comment-id=' + id + ']');
            $(block).closest('li').find(".iwd-om-pre-loader").addClass('hide');

            if(!response.allowed){
                this.updateCommentCancel();
                this.showActionsBlock(id);
                return this.showModal(response);
            }

            $(block).find('.iwd-om-message').addClass('hide');
            $(block).find('button').removeAttr('disabled').removeClass('disabled');
            $(block).find('.data-for-edit').html(response.result);
            $(block).closest('li').find('.note-list-comment').hide();
            $(block).closest('li').find('.note-list-admin-name').hide();
            $(block).show();
        },

        hideUpdateBlock: function(id) {
            var block = $(this.options.updateBlock + '[data-comment-id=' + id + ']');
            $(block).closest('li').find('.note-list-comment').show();
            $(block).closest('li').find('.note-list-admin-name').show();
            $(block).hide();
        },

        updateCommentCancel: function(id) {
            this.hideUpdateBlock(id);
            this.showActionsBlock(id);
        },

        updateCommentConfirm: function(id) {
            var self = this;

            var comment = $('textarea[name="comment[' + id + ']"]').val();
            var is_visible_on_front = $('input[name="is_visible_on_front[' + id + ']"]').prop( "checked") ? 1 : 0;

            var data = {
                'id':id,
                'is_visible_on_front':is_visible_on_front,
                'comment':comment,
                'form_key':FORM_KEY,
                'order_id':self.getCurrentOrderId()
            };

            $.ajax({
                url: this.options.urlUpdate,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
                beforeSend: function() {
                    self.beforeUpdate(id);
                }
            })
                .done(function(response) {
                    if (response.error || response.status == false){
                        self.afterUpdateFail(id, response);
                    } else {
                        self.afterUpdateSuccess(id, response);
                    }
                })
                .fail(function(error) {
                    self.afterUpdateFail(id, error);
                });
        },

        beforeUpdate: function(id){
            var block = $(this.options.updateBlock + '[data-comment-id=' + id + ']');
            $(block).find('button').attr('disabled', 'disabled').addClass('disabled');
            $(block).closest('li').find(".iwd-om-pre-loader").removeClass('hide');
            $('#history_comment_' + id).attr('disabled', 'disabled');
            $('#history_visible_' + id).attr('disabled', 'disabled');
        },

        afterUpdateFail: function(id, error){
            var block = $(this.options.updateBlock + '[data-comment-id=' + id + ']');
            $(block).find('.data-for-edit').html('');
            $(block).closest('li').find('.note-list-comment').show();
            $(block).closest('li').find('.note-list-admin-name').show();
            $(block).find('.iwd-om-message').text($t('Sorry, can not edit comment.')).addClass('hide');
            console.log(JSON.stringify(error));
        },

        afterUpdateSuccess: function(id, response){
            this.hideUpdateBlock(id);
            this.showActionsBlock(id);

            var block = $(this.options.updateBlock + '[data-comment-id=' + id + ']');
            $(block).find('button').removeAttr('disabled').removeClass('disabled');
            $(block).closest('li').find('.note-list-comment').html(response.result).show();
            $(block).closest('li').find('.note-list-admin-name').show();
            $(block).closest('li').find(".iwd-om-pre-loader").addClass('hide');
        }
    });

    return $.mage.iwdOrderManagerHistory;
});