define(
    [
        'jquery',
        'IWD_AuthCIM/js/view/payment/method-renderer/iframe',
        'Magento_Checkout/js/model/payment/additional-validators',
        'jquery/validate'
    ],
    function ($, Component, additionalValidators) {
        'use strict';

        return Component.extend({
            defaults: {
                opNumberData: '',
                opDescriptorData: '',
                opValueData: ''
            },

            idErrorMessage: '#iwd-authcim-error-message',
            isPlaceOrder: false,

            /**
             * @returns {exports}
             */
            initObservable: function () {
                var self = this;
                this._super()
                    .observe([
                        'opaqueNumber',
                        'opaqueDescriptor',
                        'opaqueValue'
                    ]);

                this.opaqueNumber.subscribe(function (value) {
                    self.opNumberData = value;
                });
                this.opaqueDescriptor.subscribe(function (value) {
                    self.opDescriptorData = value;
                });
                this.opaqueValue.subscribe(function (value) {
                    self.opValueData = value;
                });

                this.creditCardExpYear.subscribe(function (value) {
                    self.sendPaymentDataToAnet();
                });
                this.creditCardExpMonth.subscribe(function (value) {
                    self.sendPaymentDataToAnet();
                });
                this.creditCardVerificationNumber.subscribe(function (value) {
                    self.sendPaymentDataToAnet();
                });
                this.creditCardNumber.subscribe(function (value) {
                    self.sendPaymentDataToAnet();
                });

                this.initAcceptJs();
                return this;
            },

            /**
             * @returns {Object}
             */
            getData: function () {
                var data = {
                    'method': this.getCode()
                };

                if (this.creditCardId == 0) {
                    data.additional_data = {
                        'cc_id': 0,
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_save': this.creditCardSave ? 1 : 0,
                        'opaque_number': this.opaqueNumber(),
                        'opaque_descriptor': this.opaqueDescriptor(),
                        'opaque_value': this.opaqueValue()
                    };
                } else {
                    data.additional_data = {
                        'cc_id': this.creditCardId
                    }
                }

                return data;
            },

            initAcceptJs: function() {
                var self = this;
                if (self.isAcceptjsEnabled()) {
                    window.iwdAuthCIMAcceptJsResponseHandler = function (response) {
                        self.responseHandler(response);
                    };
                }
            },

            sendPaymentDataToAnet: function() {
                var secureData = {};

                secureData.cardData = {
                    cardNumber: this.creditCardNumber(),
                    month: this.creditCardExpMonth(),
                    year: this.creditCardExpYear()
                };

                if (this.isRequireCcv()) {
                    secureData.cardData.cardCode = this.creditCardVerificationNumber();
                }

                var filed = true;
                $.each(secureData.cardData, function(key,val) {if(val == 0 || val == ""){filed = false;} return filed;});
                if (!filed) {
                    return;
                }

                secureData.authData = {
                    apiLoginID: this.getApiLoginID(),
                    clientKey: this.getClientKey()
                };

                Accept.dispatchData(secureData, 'iwdAuthCIMAcceptJsResponseHandler');
            },

            /**
             * @returns {Number}
             */
            getLastCreditCard4: function(){
                var ccNumber = this.creditCardNumber();
                ccNumber = ccNumber.substr(-4, ccNumber.length);
                return (ccNumber.length == 4) ? ccNumber : 0;
            },

            /**
             * @param response
             */
            responseHandler: function(response) {
                $(this.idErrorMessage).html('').hide();

                if (response.messages.resultCode === 'Error') {
                    var errors = '';
                    for (var i = 0; i < response.messages.message.length; i++) {
                        errors += '<p>' + response.messages.message[i].text + '</p>';
                    }
                    $(this.idErrorMessage).html(errors).show();
                    this.opaqueNumber('');
                    this.opaqueDescriptor('');
                    this.opaqueValue('');
                } else {
                    this.useOpaqueData(response.opaqueData);

                    if (this.isPlaceOrder) {
                        this.placeOrder();
                    }
                }
            },

            /**
             * @param responseData
             */
            useOpaqueData: function(responseData) {
                var last4 = this.getLastCreditCard4();

                this.opaqueNumber(last4);
                this.opaqueDescriptor(responseData.dataDescriptor);
                this.opaqueValue(responseData.dataValue);
            },

            /**
             * @returns {Boolean}
             */
            isAcceptjsEnabled: function() {
                try {
                    return window.checkoutConfig.payment.iwd_authcim.isAcceptjsEnabled;
                } catch (e) {
                    return false;
                }
            },

            /**
             * @returns {Boolean}
             */
            isRequireCcv: function() {
                try {
                    return window.checkoutConfig.payment.iwd_authcim.useCvv;
                } catch (e) {
                    return false;
                }
            },

            /**
             * @returns {String}
             */
            getApiLoginID: function() {
                return window.checkoutConfig.payment.iwd_authcim.apiLoginID;
            },

            /**
             * @returns {String}
             */
            getClientKey: function() {
                return window.checkoutConfig.payment.iwd_authcim.clientKey;
            },

            placeOrder: function() {
                if (this.creditCardId == 0 && !$('#iwd_authcim_opaque_value').val()) {
                    if (this.validateHandler() && additionalValidators.validate()) {
                        this.isPlaceOrder = true;
                        this.sendPaymentDataToAnet();
                    }
                } else {
                    this.isPlaceOrder = false;
                    this._super();
                }
            }
        });
    }
);