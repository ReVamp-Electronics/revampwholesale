define([
        'jquery',
        'IWD_OrderManager/js/order/view/actions',
        'IWD_OrderManager/js/order/view/items/form',
        'mage/translate',
        'jquery/ui'
    ],

function ($, actions, form, $t) {
    'use strict';

    $.widget('mage.iwdOrderManagerItems', $.mage.iwdOrderManagerActions, {
        options: {
            urlUpdate: '',
            urlForm: '',

            cancelButtonId: '#order-items-cancel',
            updateButtonId: '#order-items-update',
            loadFromButtonId: '#iwd-om-edit-order-items-link',

            blockId: '.iwd-om-edit-order-items',
            formBlockId: '#iwd-om-edit-order-items-form',
            resultFormId: '.edit-order-table'
        },

        init: function(options){
            this.options = this._margeOptions(this.options, options);
            this._initOptions(options);
            this._initOrderItemsBlock();
            this._initActions();
        },

        _initOrderItemsBlock: function(){
            $(this.options.resultFormId).closest('.admin__table-wrapper').addClass('iwd-om-edit-order-items');
        },

        getLoadFromData:function(){
            var orderId = this.getCurrentOrderId();
            return {'form_key':FORM_KEY, 'order_id':orderId};
        },

        getConfirmUpdateData:function(){
            var data = this.getLoadFromData();
            var formData = this.getFormData(this.options.formBlockId + ' form');
            return this._margeOptions(data, formData);
        },

        _initButtonForLoadForm: function(){
            var id = this.options.loadFromButtonId.slice(1);
            var classDisallowed = (this.options.disallowed.length) ? 'class="disallowed"' : '';

            $(this.options.resultFormId).closest('.admin__page-section').find('.admin__page-section-title')
                .append('<div class="om-top-actions"><span ' + classDisallowed + ' id="' + id + '">' + $t('Edit') + '</span></div>');
            $(this.options.resultFormId).closest('.admin__table-wrapper').addClass('iwd-om-edit-order-items');
        },

        onClickAction: function(actionClass, action){
            var self = this;

            $(document).off('click touchstart', actionClass);
            $(document).on('click touchstart', actionClass, (function(e) {
                e.preventDefault();
                eval("self." + action);
            }));
        },

        getOrderIdFromUrl:function(){
            var url = location.pathname;
            var VRegExp = new RegExp(/order_id\/([0-9]+)/);
            var VResult = url.match(VRegExp);
            return VResult[1];
        },

        updateResultForm: function(response){
            this.updateHistoryBlock();
        },

        updateHistoryBlock:function(){
            var self = this;
            $(this.options.resultFormId).load(location.href + ' ' + this.options.resultFormId, {'form_key':FORM_KEY}, function(){
                $(self.options.formBlockId).hide();
                $(self.options.resultFormId).show();
                self.hidePreLoader();
            });
        },

        validateForm:function(){
            var removedItems = $('.ordered_item_remove input.remove_ordered_item, .remove_quote_item').size();
            if(removedItems != 0 && removedItems == $('.ordered_item_remove input.remove_ordered_item:checked').size()) {
                this.errorMessagePopup("Sorry, but you can not delete all items in order. Maybe, better remove this order?");
                return false;
            }

            if(window.qtyWarning > 0){
                window.qtyWarning = 0;
                this.errorMessagePopup("Warning! Requested quantity for some items are not available. Please, recheck or update changes.");
                return false;
            }

            var validator = $(this.options.formBlockId + ' form').validate();
            return validator.form();
        },

        beforeLoadFromSuccessHandler:function(){
            $('#order-items-actions-block').remove();
        },

        afterLoadFromSuccessHandler: function(){
            $('#order-items-actions-block').insertAfter(
                $(this.options.formBlockId).closest('.admin__page-section')
            );
        },


        cancelEdit:function(){
            $(this.options.formBlockId).html('').hide();
            $(this.options.resultFormId).show();
            this.scrollToBlockTop();
            $('#order-items-actions-block').remove();
        },

        showPreLoader: function(){
            $(this.options.blockId).find(".iwd-om-pre-loader").removeClass('hide');
            $('.iwd-om-actions-block').attr('disabled','disabled');
            $('.iwd-om-actions-block button').attr('disabled','disabled');

        },

        hidePreLoader: function(){
            $(this.options.blockId).find(".iwd-om-pre-loader").addClass('hide');
            $('.iwd-om-actions-block').removeAttr('disabled');
            $('.iwd-om-actions-block button').removeAttr('disabled');
        }
    });

    return $.mage.iwdOrderManagerItems;
});