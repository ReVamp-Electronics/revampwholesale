define([
        'jquery',
        'IWD_OrderManager/js/order/view/actions',
        'jquery/ui'
    ],

    function ($) {
        'use strict';

        $.widget('mage.iwdOrderManagerShipping', $.mage.iwdOrderManagerActions, {
            options: {
                urlUpdate: '',
                urlForm: '',

                cancelButtonId: '#om-edit-shipping-cancel',
                updateButtonId: '#om-edit-shipping-update',
                loadFromButtonId: '#edit-shipping-link',

                blockId: '.order-view-billing-shipping .order-shipping-method',
                formBlockId: '#shipping-result-form',
                resultFormId: '.order-view-billing-shipping .order-shipping-method .admin__page-section-item-content',

                scrollToTopAfterAction: true,
                shippingMethodBlockId: '#order-shipping-method-choose'
            },

            init: function(options){
                this.options = this._margeOptions(this.options, options);
                this._initOptions(options);
                this._initActions();
                this.initInput();
            },

            getLoadFromData:function(){
                var orderId = this.getCurrentOrderId();
                return {'form_key':FORM_KEY, order_id:orderId};
            },

            validateForm:function(){
                return $(this.options.shippingMethodBlockId).find('input[name="order[shipping_method]"]:checked').length == 1;
            },

            getConfirmUpdateData:function(){
                var self = this;
                var orderId = this.getCurrentOrderId();
                var shippingMethod = $(self.options.shippingMethodBlockId)
                    .find('input[name="order[shipping_method]"]:checked')
                    .val();

                var params = {'form_key':FORM_KEY, 'shipping_method':shippingMethod, 'order_id':orderId};

                $(['price_excl_tax', 'price_incl_tax', 'tax_percent', 'description']).each(function(j,i){
                    params[i] = $(self.options.shippingMethodBlockId)
                        .find('input[name="shipping_method[' + shippingMethod + '][' + i + ']"]')
                        .val();
                });

                return params;
            },

            initInput:function()
            {
                var self = this;
                var input = this.options.shippingMethodBlockId + ' input[type="text"]';
                var radio = this.options.shippingMethodBlockId + ' input[type="radio"]';

                $(document).off('change', radio);
                $(document).on('change', radio, function(){
                    self.showEditForm($(this));
                });

                $(document).off('change', input);
                $(document).on('change', input, function(){
                    self.editShippingMethodInfo($(this));
                });

                $(document).off('keypress', input);
                $(document).on('keypress', input, function(e){
                    if (e.which == 13 || e.which == 8) return 1;
                    var letters = '1234567890.,+-';
                    return (letters.indexOf(String.fromCharCode(e.which)) != -1);
                });
            },

            showEditForm:function(radio){
                var id = $(radio).val();
                $(this.options.shippingMethodBlockId).find('.edit_price_form').hide();
                $('#edit_price_form_' + id).show();
            },

            editShippingMethodInfo:function(input){
                var VRegExp = new RegExp(/shipping_method\[(\w+)\]\[(\w+)\]/);
                var VResult = $(input).attr('name').match(VRegExp);
                var id = VResult[1];
                var code = VResult[2];

                var form = $(this.options.shippingMethodBlockId);
                var priceExclTax = form.find('input[name="shipping_method[' + id + '][price_excl_tax]"]');
                var priceInclTax = form.find('input[name="shipping_method[' + id + '][price_incl_tax]"]');
                var taxPercent = form.find('input[name="shipping_method[' + id + '][tax_percent]"]');

                var __priceExclTax = this.evalItem(priceExclTax, 0);
                var __priceInclTax = this.evalItem(priceInclTax, 0);
                var __taxPercent = this.evalItem(taxPercent, 0);

                if(code == 'price_excl_tax' || code == 'tax_percent'){
                    var inclTax = __priceExclTax + (__priceExclTax * __taxPercent / 100);
                    priceInclTax.val(inclTax.toFixed(2));
                    priceExclTax.val(__priceExclTax.toFixed(2));
                }else if(code == 'price_incl_tax'){
                    var exclTax = __priceInclTax / (1 + __taxPercent / 100);
                    priceExclTax.val(exclTax.toFixed(2));
                    priceInclTax.val(__priceInclTax.toFixed(2));
                }
                taxPercent.val(__taxPercent.toFixed(2));
            },

            evalItem: function(item, defaultVal){
                var val = $(item).val();
                try{val = eval(val);}catch(e){}
                val = parseFloat(val);
                if(isNaN(val)){return defaultVal;}
                return val;
            }
        });

        return $.mage.iwdOrderManagerShipping;
    });