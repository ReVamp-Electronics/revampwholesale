define([
    'jquery',
    'IWD_OrderManager/js/order/view/actions',
    'jquery/ui'
],

function ($) {
    'use strict';

    $.widget('mage.iwdOrderManagerCustomer', $.mage.iwdOrderManagerActions, {
        options: {
            urlUpdate: '',
            urlForm: '',
            urlLoadCustomer: '',

            cancelButtonId: '#om-edit-account-information-cancel',
            updateButtonId: '#om-edit-account-information-update',
            loadFromButtonId: '#edit-account-information-link',

            blockId: '.order-account-information',
            formBlockId: '#account-information-result-form',
            resultFormId: '.order-account-information .admin__page-section-item-content',

            scrollToTopAfterAction: true
        },
        changingCustomer: false,

        init: function(options){
            this.options = this._margeOptions(this.options, options);
            this._initOptions(options);
            this._initActions();

            this.changeCustomer();
        },

        changeCustomer: function(){
            var self = this;

            $(document).off('click touchstart', '#om-edit-account-information-change-customer');
            $(document).on('click touchstart', '#om-edit-account-information-change-customer', function(){
                if(self.changingCustomer == true) {
                    self.updateCustomerForm();
                }else{
                    self.showChangeCustomerForm();
                }
            });
        },

        showChangeCustomerForm: function(){
            $(this.options.formBlockId + ' form .admin__field').hide();
            $(this.options.formBlockId + ' form .admin__field.field-customer_email').show();
            $(this.options.updateButtonId).closest('.actions-split').hide();
            this.scrollToBlockTop();
            this.changingCustomer = true;
        },

        updateCustomerForm: function(){
            var self = this;
            var data = this.getConfirmUpdateData();

            $.ajax({
                url: self.options.urlLoadCustomer,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
                beforeSend: function() {
                    self.showPreLoader();
                }
            })
                .done(function(response) {
                    if (response.error || response.status == false){
                        self.loadCustomerErrorHandler(response);
                    } else {
                        self.loadCustomerSuccessHandler(response);
                    }
                })
                .fail(function(error) {
                    self.loadCustomerErrorHandler(error);
                });
        },

        loadCustomerErrorHandler:function(response){
            if(response.error){
               $('#om-edit-account-information-actions-block')
                   .find('.iwd-om-message')
                   .html(response.error)
                   .removeClass('hide');
            }

            this.hidePreLoader();
        },

        loadCustomerSuccessHandler:function(response)
        {
            this.fillCustomerForm(response);
            this.cancelChangeCustomer();

            this.hidePreLoader();
        },

        fillCustomerForm:function(response)
        {
            var self= this;
            $.each(response, function(i, val){
                $(self.options.formBlockId + ' [name="customer_info[' + i + ']"]').val(val);
            });
        },

        cancelChangeCustomer: function()
        {
            $(this.options.formBlockId + ' form .admin__field').show();
            $(this.options.updateButtonId).closest('.actions-split').show();
            this.changingCustomer = false;
            $('#om-edit-account-information-actions-block').find('.iwd-om-message').addClass('hide');
        },

        getLoadFromData:function(){
            var orderId = this.getCurrentOrderId();
            return {'form_key':FORM_KEY, order_id:orderId};
        },

        getConfirmUpdateData:function(){
            var orderId = this.getCurrentOrderId();
            var data = {'form_key':FORM_KEY, order_id:orderId};
            $('#order_customer_info_form').serializeArray().map(function(x){data[x.name] = x.value;});
            return data;
        },

        cancelEdit: function(){
            if(this.changingCustomer == true) {
                this.cancelChangeCustomer();
            }else{
                $(this.options.formBlockId).html('').hide();
                $(this.options.resultFormId).show();
                this.scrollToBlockTop();
            }
        }
    });

    return $.mage.iwdOrderManagerCustomer;
});