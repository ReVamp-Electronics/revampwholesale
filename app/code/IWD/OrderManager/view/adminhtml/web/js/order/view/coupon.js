define([
        'jquery',
        'IWD_OrderManager/js/order/view/coupon',
        'jquery/ui'
    ],

    function ($) {
        'use strict';

        $.widget('mage.iwdOrderManagerCoupon', $.mage.iwdOrderManagerActions, {
            options: {
                urlUpdate: '',

                cancelButtonId: '#iwd_om_coupon_remove',
                updateButtonId: '#iwd_om_coupon_apply',

                couponCode: '#iwd_om_coupon',

                blockId: '#iwd-om-coupon-section',
                formBlockId: '#iwd-om-coupon-section',
                resultFormId: '',

                scrollToTopAfterAction: false
            },
            couponCode: '',

            init: function (options) {
                this.options = this._margeOptions(this.options, options);
                this._initOptions(options);

                this._initCancel();
                this._initUpdate();

                this.moveSectionToNeededPlace();
                this.initCouponField();
            },

            initCouponField: function() {
                var self = this;

                this.couponCode = $(self.options.couponCode).val();
                self.disableApplyButton();

                $(document).on('keyup change', self.options.couponCode, function () {
                    var couponCode = $(self.options.couponCode).val();
                    if (couponCode.length == 0 || self.couponCode == couponCode) {
                        self.disableApplyButton();
                    } else {
                        self.enableApplyButton();
                    }
                });
            },

            enableApplyButton: function () {
                $(this.options.updateButtonId).removeClass('disabled').removeAttr('disabled');
            },

            disableApplyButton: function () {
                $(this.options.updateButtonId).addClass('disabled').attr('disabled', 'disabled');
            },

            moveSectionToNeededPlace: function() {
                var section = $('#iwd-om-coupon-section');
                $('.order-totals').closest('section').before($(section));
                $(section).show();
            },

            cancelEdit: function() {
                $(this.options.couponCode).val('');
                this.confirmUpdate();
            },

            getConfirmUpdateData:function() {
                var orderId = this.getCurrentOrderId();
                var coupon = $(this.options.couponCode).val();
                return {'form_key':FORM_KEY, order_id:orderId, coupon:coupon};
            },

            confirmUpdateSuccessHandler: function(response){
                this.hidePreLoader();

                if (response.message) {
                    $(this.options.formBlockId + ' .message').remove();
                    var cssClass = response.is_error ? 'message-error' : 'message-success';
                    $(this.options.formBlockId + ' #iwd-om-coupon-form').before('<div class="message ' + cssClass + ' iwd-om-message-notice"><div class="message-inner"><div class="message-content">' + response.message + '</div></div></div>');
                    if(response.result == 'reload') {
                        setTimeout(function () {
                            location.reload();
                        }, 1000);
                    }
                } else {
                    if(response.result == 'reload'){
                        location.reload();
                    }
                }
            }
        });

        return $.mage.iwdOrderManagerCoupon;
    });