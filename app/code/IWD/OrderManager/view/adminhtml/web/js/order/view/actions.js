define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery/validate',
    'jquery/ui'
],

function($, modal, $t){
    'use strict';

    $.widget('mage.iwdOrderManagerActions', {
        options: {
            urlUpdate: '',
            urlForm: '',

            cancelButtonId: '',
            updateButtonId: '',
            loadFromButtonId: '',

            blockId: '',
            formBlockId: '',
            resultFormId: '',

            scrollToTopAfterAction: false,
            disallowed: [],
            initButtonForLoad: true
        },
        additionalAction: false,

        _initOptions: function(options) {
            var self = this;
            options = options || {};
            $.each(options, function(i, e){self.options[i] = e;});
        },

        _margeOptions: function(options1, options2) {
            $.each(options2, function(i, e){options1[i] = e;});
            return options1;
        },

        _initActions: function(){
            this._addFormBlock();
            if(this.options.initButtonForLoad) {
                this._initButtonForLoadForm();
                this._initLoadForm();
            }
            this._initCancel();
            this._initUpdate();
            this._initCheckboxes();
        },

        _initCheckboxes:function(){
            $(document).off('change', '.iwd-om-actions-block .dropdown-menu input:checkbox');
            $(document).on('change', '.iwd-om-actions-block .dropdown-menu input:checkbox', (function() {
                if ($(this).prop("checked") == true) {
                    $(this).closest('label').addClass('checked');
                } else {
                    $(this).closest('label').removeClass('checked');
                }
            }));
        },

        _initChangeFormEvent:function(){
            var self = this;
            var inputs = self.options.formBlockId + ' input, ' + self.options.formBlockId + ' select, ' + self.options.formBlockId + ' radio';

            var startFormHash = '';
            setTimeout(function(){
                startFormHash = self._formValue();
            }, 1000);

            $(document).off('change', inputs);
            $(document).on('change', inputs, function(){
                if (startFormHash == self._formValue()) {
                    $(self.options.updateButtonId).addClass('disabled');
                    $(self.options.updateButtonId).siblings('button').addClass('disabled');
                } else {
                    $(self.options.updateButtonId).removeClass('disabled');
                    $(self.options.updateButtonId).siblings('button').removeClass('disabled');
                }
            });
        },

        _formValue:function()
        {
            var val = '';
            $(this.options.formBlockId + ' form').serializeArray().map(function(x){
                if(x.name.indexOf('back_to_stock') == -1){
                    val = val + x.name + x.value;
                }
            });
            return val;
        },

        _initButtonForLoadForm: function(){
            var id = this.options.loadFromButtonId.slice(1);
            var classDisallowed = (this.options.disallowed.length) ? 'class="disallowed"' : '';

            $(this.options.blockId).find('.admin__page-section-item-title .title')
                .append('<div class="om-top-actions"><span ' + classDisallowed + ' id="' + id + '">' + $t('Edit') + '</span></div>');
        },

        _initCancel: function(){
            var self = this;
            $(document).off('click touchstart', this.options.cancelButtonId);
            $(document).on('click touchstart', this.options.cancelButtonId, (function(e) {
                e.preventDefault();
                self.cancelEdit();
            }));
        },

        _initUpdate: function(){
            var self = this;
            $(document).off('click touchstart', this.options.updateButtonId);
            $(document).on('click touchstart', this.options.updateButtonId, (function(e) {
                e.preventDefault();

                if(self.validateForm()){
                    self.confirmUpdate();
                }
            }));
        },

        _initLoadForm: function(){
            var self = this;
            $(document).off('click touchstart', this.options.loadFromButtonId);
            $(document).on('click touchstart', this.options.loadFromButtonId, (function(e) {
                e.preventDefault();
                if(self.options.disallowed.length > 0){
                    self.errorMessagePopup(self.options.disallowed[0]);
                }else{
                    self.loadFrom();
                }
            }));
        },

        _addFormBlock:function(){
            var formId = this.options.formBlockId.substr(1);
            $(this.options.blockId)
                .append('<div id="' + formId + '" class="iwd-om-edit-form"></div>' +
                '<div class="iwd-om-pre-loader hide"><i class="loader"></i></div>');
        },

        cancelEdit:function(){
            $(this.options.formBlockId).html('').hide();
            $(this.options.resultFormId).show();
            $(this.options.formBlockId).find('.iwd-om-message').addClass('hide');

            this.scrollToBlockTop();
        },

        scrollToBlockTop:function(){
            if(this.options.scrollToTopAfterAction){
                var top = $(this.options.blockId).offset().top - 80;
                top = top > 0 ? top : 0;
                $('html, body').animate({scrollTop:top}, 200);
            }
        },

        confirmUpdate:function(additionalData){
            var self = this;
            var data = this.getConfirmUpdateData();
            additionalData = additionalData || {};
            $.each(additionalData, function(i, e){data[i] = e;});

            $.ajax({
                url: self.options.urlUpdate,
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
                        self.confirmUpdateErrorHandler(response);
                    } else {
                        self.confirmUpdateSuccessHandler(response);
                    }
                })
                .fail(function(error) {
                    self.confirmUpdateErrorHandler(error);
                });
        },

        confirmUpdateErrorHandler: function(response){
            console.log(JSON.stringify(response));
            this.hidePreLoader();

            if(response.error){
                $(this.options.formBlockId).find('.iwd-om-message').html(response.error).removeClass('hide');
            }
        },

        confirmUpdateSuccessHandler: function(response){
            if(response.result == 'multistock') {
                this.showMultistockModal(response);
            } else if(response.result == 'reload') {
                location.reload();
            } else if(response.result == 'additional_info') {
                this.showModalWithAdditionalInfo(response);
            } else {
                this.updateResultForm(response);
            }
        },

        showMultistockModal: function(response){
            $(document).trigger("showMultistockModalEvent", [response]);
        },

        showModalWithAdditionalInfo: function(response){
            var self = this;
            this.additionalAction = false;

            modal({
                title: response.title,
                content: response.form,
                modalClass: "iwd-order-manager-popup without-close-button",
                closeOnEscape: false,
                clickableOverlay: false,
                closed: function(){
                    self.additionalInfoCancel();
                },
                buttons: [
                    {
                        text: $t('Update'),
                        class: 'iwd-om-popup-upgrade',
                        click: function() {
                            if (self.additionalInfoUpdate()) {
                                this.closeModal();
                            }
                        }
                    },
                    {
                        text: $t('Do Not Touch'),
                        class: 'iwd-om-popup-cancel',
                        click: function() {
                            this.closeModal();
                        }
                    }
                ]
            });
        },

        additionalInfoUpdate:function()
        {
            if (iwdOrderManagerAdditional.validateForm()) {
                iwdOrderManagerAdditional.confirmUpdate();
                this.showPreLoader();
                this.additionalAction = true;
                return true;
            }
            return false;
        },

        additionalInfoCancel:function()
        {
            if (this.additionalAction == false) {
                this.additionalAction = true;
                iwdOrderManagerAdditional.confirmUpdate({'skip_save': '1'});
                this.showPreLoader();
            }
        },

        updateResultForm:function(response){
            $(this.options.resultFormId).html(response.result).show();
            $(this.options.formBlockId).html('').hide();

            this.scrollToBlockTop();
            this.hidePreLoader();
        },

        loadFrom: function(){
            var self = this;
            var data = this.getLoadFromData();

            $.ajax({
                url: this.options.urlForm,
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
                        self.loadFromErrorHandler(response);
                    } else {
                        if(!response.allowed){
                            self.hidePreLoader();
                            return self.showModal(response);
                        }
                        self.loadFromSuccessHandler(response);
                    }
                })
                .fail(function(error) {
                    self.loadFromErrorHandler(error);
                });
        },

        loadFromErrorHandler: function(response){
            console.log(JSON.stringify(response));
            this.hidePreLoader();
        },

        loadFromSuccessHandler: function(response){
            this.beforeLoadFromSuccessHandler();

            $(this.options.formBlockId).html(response.result).show();
            $(this.options.resultFormId).hide();

            this.afterLoadFromSuccessHandler();

            this._initChangeFormEvent();

            this.hidePreLoader();
        },

        getFormData: function(form){
            var unindexed_array = $(form).serializeArray();
            var indexed_array = {};

            $.map(unindexed_array, function(n){
                indexed_array[n['name']] = n['value'];
            });

            return indexed_array;
        },

        showModal:function (response){
            $('.iwd-order-manager-popup').removeClass('_show').addClass('_hide');

            var buttons = [
                {
                    text: $t('Ok'),
                    class: 'iwd-om-popup-ok',
                    click: function() {
                        this.closeModal();
                    }
                }
            ];

            if (response.ext_url) {
                buttons.push({
                    text: $t('Upgrade'),
                    class: 'iwd-om-popup-upgrade',
                    click: function() {
                        window.open(response.ext_url, '_blank');
                        this.closeModal();
                    }
                });
            }

            modal({
                title: $t('Order Manager'),
                content: response.result,
                modalClass: "iwd-order-manager-popup",
                clickableOverlay: false,
                buttons:buttons
            });
        },

        getCurrentOrderId:function(){
            var VRegExp = new RegExp(/order_id\/([0-9]+)/);
            var VResult = window.location.href.match(VRegExp);
            return VResult[1];
        },

        errorMessagePopup: function(message){
            modal({
                title: $t('Order Manager'),
                content: $t(message),
                modalClass: "iwd-order-manager-popup",
                clickableOverlay: false,
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

        // CAN rewrite
        showPreLoader: function(){
            $(this.options.blockId).find(".iwd-om-pre-loader").removeClass('hide');
            $('.iwd-om-actions-block').attr('disabled','disabled');
        },

        hidePreLoader: function(){
            $(this.options.blockId).find(".iwd-om-pre-loader").addClass('hide');
            $('.iwd-om-actions-block').removeAttr('disabled');
        },

        validateForm: function(){
            var validator = $(this.options.formBlockId + ' form').validate();
            return validator.form();
        },

        beforeLoadFromSuccessHandler: function(){

        },

        afterLoadFromSuccessHandler: function(){

        },

        // NEED rewrite
        getLoadFromData:function(){ return {}; },
        getConfirmUpdateData:function(){ return {}; }
    });

    return $.mage.iwdOrderManagerActions;
});