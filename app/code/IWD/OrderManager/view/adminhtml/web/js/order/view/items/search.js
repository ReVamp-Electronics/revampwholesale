define([
        "jquery",
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        "jquery/ui",
        "Magento_Catalog/catalog/product/composite/configure",
        'Magento_Sales/order/create/scripts'
    ],

function ($, modal, $t) {
    'use strict';

    $.widget('mage.iwdOrderManagerItemsSearch', {
        options: {
            searchButtonId: '#order-items-add-products',
            searchCancelId: '#iwd-om-cancel-add-products',
            searchUpdateId: '#iwd-om-update-add-products',

            removeQuoteItemButton: '.remove_quote_item',

            searchProductsUrl: '',
            addProductsUrl: '',
            baseUrl: '',
            removeQuoteItemUrl: '',

            blockId: '.iwd-om-edit-order-items',
            formBlockId: '#iwd-om-edit-order-items-form',
            resultFormId: '.edit-order-table'
        },

        init: function(options){
            this._initOptions(options);

            this.initSearchAction();
            this.initCancelAction();
            this.initUpdateAction();
            this.initRemoveNewQuoteItems();
        },

        _initOptions: function(options) {
            var self = this;
            options = options || {};
            $.each(options, function(i, e){self.options[i] = e;});
        },

        initSearchAction:function(){
            var self = this;
            $(document).off('click touchstart', this.options.searchButtonId);
            $(document).on('click touchstart', this.options.searchButtonId, (function(e) {
                e.preventDefault();
                self.initOrderAdmin();
                self.searchProducts();
            }));
        },

        initCancelAction:function(){
            var self = this;
            $(document).off('click touchstart', this.options.searchCancelId);
            $(document).on('click touchstart', this.options.searchCancelId, (function(e) {
                e.preventDefault();
                self.removeProductsGrid();
            }));
        },

        initUpdateAction:function(){
            var self = this;
            $(document).off('click touchstart', this.options.searchUpdateId);
            $(document).on('click touchstart', this.options.searchUpdateId, (function(e) {
                e.preventDefault();
                self.productGridAddSelected();
            }));
        },

        initRemoveNewQuoteItems:function(){
            var self = this;
            $(document).off('click touchstart', this.options.removeQuoteItemButton);
            $(document).on('click touchstart', this.options.removeQuoteItemButton, (function(e) {
                e.preventDefault();
                self.removeNewQuoteItem(this);
            }));
        },

        removeNewQuoteItem:function(item)
        {
            var id = $(item).data('id');
            var self = this;

            if($(item).hasClass('remove_quote_item_error')){
                self.removeQuoteItemSuccessHandler(id);
                return;
            }

            $.ajax({
                url: this.options.removeQuoteItemUrl,
                data: {'form_key':FORM_KEY, 'id':id},
                type: 'post',
                dataType: 'json',
                context: this,
                beforeSend: function() {
                    self.showPreLoader();
                }
            })
                .done(function(response) {
                    if (response.error || response.status == false){
                        self.ajaxResponseErrorHandler(response);
                    } else {
                        self.removeQuoteItemSuccessHandler(id);
                    }
                })
                .fail(function(error) {
                    self.ajaxResponseErrorHandler(error);
                });
        },

        removeQuoteItemSuccessHandler:function(id){
            $(this.options.formBlockId + ' tr[data-item-id="' + id + '"]').remove();
            $(this.options.formBlockId + ' tr[data-parent-id="' + id + '"]').remove();
            this.hidePreLoader();
        },

        initOrderAdmin:function(){
            var config = {};
            var baseUrl = this.options.baseUrl;

            var order = new AdminOrder(config);
            order.setLoadBaseUrl(baseUrl);

            window.order = order;
        },

        searchProducts:function() {
            var self = this;

            $.ajax({
                url: this.options.searchProductsUrl,
                data: {'form_key':FORM_KEY},
                type: 'post',
                dataType: 'json',
                context: this,
                beforeSend: function() {
                    self.showPreLoader();
                }
            })
                .done(function(response) {
                    if (response.error || response.status == false){
                        self.ajaxResponseErrorHandler(response);
                    } else {
                        self.searchProductsSuccessHandler(response);
                    }
                })
                .fail(function(error) {
                    self.ajaxResponseErrorHandler(error);
                });
        },

        ajaxResponseErrorHandler: function(response){
            var message = typeof(response.error) == "string" ? response.error : 'Error! Can not get response.';
            this.errorMessagePopup(message);
            console.log(JSON.stringify(response));
            this.hidePreLoader();
        },

        errorMessagePopup: function(message){
            modal({
                title: $t('Order Manager'),
                content: $t(message),
                modalClass: "iwd-order-manager-popup",
                buttons:[
                    {
                        text: $t('Ok'),
                        class: 'iwd-order-manager-popup',
                        click: function() {
                            this.closeModal();
                        }
                    }
                ]
            })
        },

        searchProductsSuccessHandler: function(response){
            $(this.options.blockId + ' #iwd-om-search-products').remove();
            $(this.options.blockId).append('<div id="iwd-om-search-products">' + response.search_grid + '</div>');

            this.hidePreLoader();
            $('#order-items-actions-block').hide();
        },


        productGridAddSelected: function(){
            var orderId = this.getCurrentOrderId();
            var listType = 'product_to_add';

            // prepare fields
            var fieldsPrepare = {};
            var itemsFilter = [];
            var products = order.gridProducts.toObject();

            if(Object.getOwnPropertyNames(products).length === 0){
                return;
            }

            for (var productId in products) {
                itemsFilter.push(productId);
                var paramKey = 'item['+productId+']';
                for (var productParamKey in products[productId]){
                    paramKey += '['+productParamKey+']';
                    fieldsPrepare[paramKey] = products[productId][productParamKey];
                }
            }
            fieldsPrepare.order_id = orderId;
            fieldsPrepare.form_key = FORM_KEY;

            // create fields
            var fields = [];
            for (var name in fieldsPrepare) {
                fields.push(new Element('input', {type:'hidden', name:name, value:fieldsPrepare[name]}));
            }
            productConfigure.addFields(fields);

            // filter items
            if (itemsFilter) {
                productConfigure.addItemsFilter(listType, itemsFilter);
            }

            // prepare and do submit
            var self = this;
            productConfigure.addListType(listType, {urlSubmit: self.options.addProductsUrl});
            productConfigure.setOnLoadIFrameCallback(listType, function(response){
                self.loadAreaResponseHandler(response);
            }.bind(this));
            productConfigure.submit(listType);
        },

        loadAreaResponseHandler:function(response){
            if (response) {
                if(response.status == true){
                    $(this.options.formBlockId + ' .iwd-om-edit-order-table > tbody').append(response.result);
                    $('#iwd-om-edit-order-items-form').find('.edit_order_item.qty_input').each(function(){
                        $(this).change();
                    });
                } else {
                    var errorMessage = response.error ? response.error : '';
                    this.errorMessagePopup(errorMessage);
                }
            } else {
                console.log('Can not get response.');
            }

            this.removeProductsGrid();
        },

        removeProductsGrid: function(){
            $(this.options.blockId + ' #iwd-om-search-products').remove();
            $('#order-items-actions-block').show();
        },

        showPreLoader: function(){
            $(this.options.blockId).find(".iwd-om-pre-loader").removeClass('hide');
            $('.iwd-om-actions-block button').attr('disabled','disabled');
        },

        hidePreLoader: function(){
            $(this.options.blockId).find(".iwd-om-pre-loader").addClass('hide');
            $('.iwd-om-actions-block button').removeAttr('disabled');
        },

        getCurrentOrderId:function()
        {
            var VRegExp = new RegExp(/order_id\/([0-9]+)/);
            var VResult = window.location.href.match(VRegExp);
            return VResult[1];
        }
    });

    return $.mage.iwdOrderManagerItemsSearch;
});