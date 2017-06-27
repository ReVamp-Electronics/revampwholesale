define([
        'jquery',
        'jquery/ui'
    ],
    function ($) {
        'use strict';

        $.widget('mage.iwdAuthCimAcceptJs', {
            options: {
                acceptEnabled: false,
                apiLoginId: '',
                acceptKey: '',
                ccNumber: '#iwd_authcim_cc_number',
                ccExpMonth: '#iwd_authcim_expiration',
                ccExpYear: '#iwd_authcim_expiration_yr',
                ccCode: '#iwd_authcim_cc_cid',
                hasVerification: false,
                opaqueNumber: '#iwd_authcim_opaque_number',
                opaqueDescriptor: '#iwd_authcim_opaque_descriptor',
                opaqueValue: '#iwd_authcim_opaque_value',
                idErrorMessage: '#iwd_authcim_error_message'
            },

            init: function (options) {
                this.initOptions(options);
                this.initAcceptJs();
            },

            initOptions: function(options) {
                var self = this;
                options = options || {};
                $.each(options, function(i, e){self.options[i] = e;});
            },

            initAcceptJs: function () {
                var self = this;
                if (!self.options.acceptEnabled) {
                    return;
                }

                window.iwdAuthCIMAcceptJsResponseHandler = function(response){
                    self.responseHandler(response);
                };

                var selector = [this.options.ccNumber, this.options.ccExpMonth, this.options.ccExpYear, this.options.ccCode].join();
                $(document).on('change', selector, function(){
                    self.sendPaymentDataToAnet();
                });

                $(document).on('requestAcceptJs', function () {
                    self.sendPaymentDataToAnet();
                });
            },

            responseHandler: function (response) {
                $(this.options.idErrorMessage).html('').hide();

                if (response.messages.resultCode === 'Error') {
                    var errors = '';
                    for (var i = 0; i < response.messages.message.length; i++) {
                        errors += '<p>' + response.messages.message[i].text + '</p>';
                    }
                    $(this.options.idErrorMessage).html(errors).show();
                    $(this.options.opaqueNumber).val('');
                    $(this.options.opaqueDescriptor).val('');
                    $(this.options.opaqueValue).val('');
                } else {
                    var ccNumber = $(this.options.ccNumber).val();
                    ccNumber = ccNumber.substr(-4, ccNumber.length);
                    ccNumber = (ccNumber.length == 4) ? ccNumber : 0;
                    $(this.options.opaqueNumber).val(ccNumber);
                    $(this.options.opaqueDescriptor).val(response.opaqueData.dataDescriptor);
                    $(this.options.opaqueValue).val(response.opaqueData.dataValue);

                    $(document).trigger('responseAcceptJs');
                }
            },

            sendPaymentDataToAnet: function() {
                var secureData = {}, authData = {}, cardData = {};

                cardData.cardNumber = $(this.options.ccNumber).val();
                cardData.month = $(this.options.ccExpMonth).val();
                cardData.year = $(this.options.ccExpYear).val();
                if (this.options.hasVerification) {
                    cardData.cardCode = $(this.options.ccCode).val();
                }

                var filed = true;
                $.each(cardData, function(key,val) {if(val == 0 || val == ""){filed = false;} return filed;});
                if (!filed) {
                    return;
                }

                secureData.cardData = cardData;

                authData.clientKey = this.options.acceptKey;
                authData.apiLoginID = this.options.apiLoginId;
                secureData.authData = authData;

                Accept.dispatchData(secureData, 'iwdAuthCIMAcceptJsResponseHandler');
            }
        });

        return $.mage.iwdAuthCimAcceptJs;
    });
