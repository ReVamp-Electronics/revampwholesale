define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function ($,
              Component,
              placeOrderAction,
              setPaymentInformationAction,
              fullScreenLoader,
              additionalValidators,
              redirectOnSuccessAction
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'IWD_AuthCIM/payment/iframe',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.',
                code: 'iwd_authcim',
                creditCardId: 0,
                isSavedCc: false,
                creditCardSave: false
            },
            placeOrderHandler: null,
            validateHandler: null,
            redirectAfterPlaceOrder: true,

            /**
             * @returns {exports}
             */
            initObservable: function () {
                var self = this;

                this._super()
                    .observe([
                        'active',
                        'savedCc',
                        'isSavedCc',
                        'creditCardSave'
                    ]);

                this.savedCc.subscribe(function (value) {
                    self.creditCardId = value;
                    self.isSavedCc(value != 0);
                });
                this.isSavedCc(this.creditCardId != 0);

                this.creditCardSave.subscribe(function (value) {
                    self.creditCardSave = value;
                });

                return this;
            },

            /**
             * @override
             */
            placeOrder: function () {
                var self = this;

                if (this.validateHandler() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    fullScreenLoader.startLoader();
                    $.when(
                        setPaymentInformationAction(this.messageContainer, self.getData())
                    ).done(
                        function(){
                            self.getPlaceOrderDeferredObject()
                                .fail(
                                    function () {
                                        self.isPlaceOrderActionAllowed(true);
                                        fullScreenLoader.stopLoader();
                                    }
                                ).done(
                                    function () {
                                        self.afterPlaceOrder();
                                        if (self.redirectAfterPlaceOrder) {
                                            redirectOnSuccessAction.execute();
                                        }
                                    }
                                );
                        }
                    ).fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader();
                        }
                    ).always(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                            fullScreenLoader.stopLoader();
                        }
                    );
                }
            },

            /**
             * @returns {*}
             */
            getPlaceOrderDeferredObject: function () {
                return $.when(
                    placeOrderAction(this.getData(), this.messageContainer)
                );
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
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_number': this.creditCardNumber()
                    };
                } else {
                    data.additional_data = {
                        'cc_id': this.creditCardId
                    }
                }

                return data;
            },

            /**
             * @param {Object} handler
             */
            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            /**
             * @param {Object} handler
             */
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            /**
             * @returns {Object}
             */
            context: function () {
                return this;
            },

            /**
             * @returns {Boolean}
             */
            isShowLegend: function () {
                return true;
            },

            /**
             * @returns {String}
             */
            getCode: function () {
                return this.code;
            },

            /**
             * @returns {Boolean}
             */
            isActive: function () {
                try {
                    return window.checkoutConfig.payment.iwd_authcim.isActive;
                } catch (e) {
                    return false;
                }
            },

            /**
             * @returns {Boolean}
             */
            isAllowToChooseSaveCc: function () {
                try {
                    return !window.checkoutConfig.payment.iwd_authcim.isSaveCc;
                } catch (e) {
                    return false;
                }
            },

            /**
             * @returns {Boolean}
             */
            isGuestCheckout: function() {
                try {
                    return window.checkoutConfig.payment.iwd_authcim.isGuestCheckout;
                } catch (e) {
                    return true;
                }
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
             * @returns {Object}
             */
            getSavedCcList: function () {
                try {
                    return window.checkoutConfig.payment.iwd_authcim.savedCcList;
                } catch (e) {
                    return {};
                }
            },

            /**
             * @returns {Object}
             */
            getSavedCcListValues: function () {
                return _.map(this.getSavedCcList(), function (value, key) {
                    return {
                        'value': key,
                        'card': value
                    };
                });
            }
        });
    }
);