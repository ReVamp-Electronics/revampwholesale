define([
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'jquery/validate'
    ],
    function ($, modal, $t) {
        'use strict';

        $.widget('mage.iwdAuthCimCustomerCards', {
            options: {
                cardListItem : '.iwd-cim-card-list-item',

                cancelEditCc : '.iwd_cim_cancel_save_cc',
                editCc : '.iwd_cim_save_cc',
                addNewCc : '#iwd_cim_add_cc',

                removeStep1 : '.action-delete',
                removeStep2 : '.action-delete-2',
                removeCancel : '.action-cancel-delete',

                actionEditCc : '.action-edit',
                actionSaveEditCc : '.action-save',
                actionCancelEditCc : '.action-cancel',
                actionEnableCc : '.action-enable',

                profileIdInput : '#authorize_profile_id',
                profileIdButton : '#authorize_profile_id_button',

                profileForm: '.address-item-edit-content',
                addressForm : '#iwd_cim_address_form',
                paymentForm : '#iwd_cim_payment_form',
                cardHash : '#iwd_cc_hash',

                deleteUrl : '#',
                addUrl : '#',
                updateUrl : '#',
                statusUrl : '#',
                syncUrl : '#',

                acceptEnabled: false,
                apiLoginId: '',
                acceptKey: ''
            },

            init: function (options) {
                this.initOptions(options);

                this.startEditProfile();
                this.cancelEditProfile();
                this.finishEditProfile();

                this.removeProfileStep1();
                this.removeProfileStep2();
                this.cancelRemoveProfile();

                this.enableProfile();

                this.addNewProfile();

                this.syncProfile();

                this.initAcceptJs();
            },

            /**** ENABLE/DISABLE PROFILE ****/
            enableProfile: function () {
                var self = this;
                $(document).on('click', this.options.actionEnableCc, function () {
                    var checkbox = this;
                    self.ajaxRequest(
                        self.options.statusUrl,
                        {'status' : checkbox.checked, 'hash' : self.getCardItemId(this)},
                        function (response) {
                            self.showSuccessMessage($t('Card status was changed'));
                        },
                        function (response) {
                            checkbox.checked = !checkbox.checked;
                            self.showErrorMessage(response);
                        }
                    );
                });
            },

            /**** EDIT EXISTING PROFILE ****/
            startEditProfile: function () {
                var self = this;
                $(document).on('click', this.options.actionEditCc, function () {
                    self.showProfileForm();
                    self.unselectAllCards();
                    self.getCardItem(this).addClass('ui-state-active');
                    var id = self.getCardItemId(this);
                    var card = window.iwdAuthcimCards[id];
                    if (card) {
                        self.fillAddressForm(card);
                        self.fillPaymentForm(card);
                        self.setTitleForEditCard('Edit Card');
                    }

                    var cardItem = self.getCardItem(this);
                    $(cardItem).find(self.options.actionSaveEditCc).show();
                    $(cardItem).find(self.options.actionCancelEditCc).show();
                    $(cardItem).find(self.options.actionEditCc).hide();

                    $(self.options.cardHash).val(id);
                });
            },
            fillAddressForm: function (card) {
                $(this.options.addressForm + ' input').each(function () {
                    try {
                        var re = /address\[(.*)\]/ig;
                        var name = re.exec($(this).attr('name'));
                        $(this).val(card.address[name[1]]);
                    } catch (e) {
                        console.log(e);
                    }
                });

                $('#iwd_cim_address_country_id').val(card.address['country_id']).change();
                $('#iwd_cim_address_region_id').val(card.address['region_id']).change();
            },
            fillPaymentForm: function (card) {
                $('#iwd_authcim_cc_type').val(card.payment['cc_type']).change();
                $('#iwd_authcim_expiration').val(card.payment['cc_exp_month']).change();
                $('#iwd_authcim_expiration_yr').val(card.payment['cc_exp_year']).change();
            },
            setTitleForEditCard: function(text) {
                var title = $(this.options.addressForm + ' > .block-title strong');
                if (title) {
                    title.text($t(text));
                }
            },

            /**** CREATE NEW / CANCEL EDIT PROFILE ****/
            addNewProfile: function () {
                var self = this;
                $(document).on('click', this.options.addNewCc, function () {
                    self.showProfileForm();
                    self.unselectAllCards();
                    self.clearProfileForm();
                    self.setTitleForEditCard('Add New Card');
                    $('#authcim_address_firstname').focus();
                });
            },
            cancelEditProfile: function () {
                var self = this;
                $(document).on('click', this.options.cancelEditCc, function () {
                    self.hideProfileForm();
                    self.unselectAllCards();
                    self.clearProfileForm();
                });
            },
            clearProfileForm: function () {
                var customerId = $('#iwd_customer_id').val();
                $(this.options.addressForm + ' input').each(function () {
                    $(this).val('');
                });
                $('#iwd_cim_address_country_id').val("US").change();
                $('#iwd_cim_address_region_id').val(1).change();

                $(this.options.paymentForm + ' input').each(function () {
                    $(this).val('');
                });
                $(this.options.paymentForm + ' select').each(function () {
                    $(this).val($(this).find("option:first").val());
                });
                $('label.error').remove();

                $('#iwd_customer_id').val(customerId);
                $(this.options.cardHash).val(0);
            },
            showProfileForm: function() {
                $(this.options.profileForm).show();
            },
            hideProfileForm: function() {
                $(this.options.profileForm).hide();
            },

            /**** UPDATE / CREATE PROFILE ****/
            finishEditProfile: function () {
                var self = this;
                var form = self.options.profileForm + ' form';

                $(document).on('click', this.options.editCc, function () {
                    var status = $(form).validate().form();
                    $(form + " input, " + form + " select")
                        .off('change')
                        .on('change', function () {
                            $(form).validate().form();
                        });

                    if (status) {
                        if (self.options.acceptEnabled && !$('#iwd_authcim_opaque_value').val()) {
                            $(document).trigger('requestAcceptJs');
                            $(document).off('responseAcceptJs');
                            $(document).on('responseAcceptJs', function () {
                                self.sendAjaxRequestForUpdate();
                            });
                        } else {
                            self.sendAjaxRequestForUpdate();
                        }
                    }
                });
            },

            sendAjaxRequestForUpdate: function() {
                var self = this;
                self.ajaxRequest(
                    self.options.updateUrl,
                    self.getFormData(self.options.profileForm + ' form'),
                    function (response) {
                        if (response.status == true) {
                            $('ul.profile-list').replaceWith(response.list_html);
                            self.hideProfileForm();
                            self.clearProfileForm();
                            self.showSuccessMessage($t('Payment profile was successfully saved'));
                        } else {
                            self.showErrorMessage($t('Issue during save profile'));
                        }
                    },
                    function (response) {
                        self.showErrorMessage(response);
                    }
                );
            },

            /**** REMOVE PAYMENT PROFILE ****/
            removeProfileStep1: function () {
                var self = this;
                $(document).on('click', this.options.removeStep1, function () {
                    self.removeProfileCanceled();
                    var cardItem = self.getCardItem(this);
                    $(cardItem).find(self.options.removeStep2).show();
                    $(cardItem).find(self.options.removeCancel).show();
                    $(cardItem).find(self.options.removeStep1).hide();
                });
            },
            removeProfileStep2: function () {
                var self = this;
                $(document).on('click', this.options.removeStep2, function () {
                    var card = self.getCardItem(this);
                    var id = self.getCardItemId(this);
                    self.removePaymentProfileWithAlert(card, id);
                });
            },
            cancelRemoveProfile: function() {
                var self = this;
                $(document).on('click', this.options.removeCancel, function () {
                    self.removeProfileCanceled();
                });
            },
            removeProfileCanceled: function() {
                $(this.options.removeStep2).hide();
                $(this.options.removeCancel).hide();
                $(this.options.removeStep1).show();
            },
            removePaymentProfileWithAlert: function (card, id) {
                var self = this;
                modal({
                    title: 'Warning!',
                    content: $t('Do you want to remove saved payment profile (saved credit card)? Payment profile will be removed from Authorize.net server too.'),
                    closeOnEscape: false,
                    clickableOverlay: false,
                    buttons: [
                        {
                            text: $t('Yes, remove'),
                            click: function() {
                                self.removePaymentProfile(card, id);
                                this.closeModal();
                            }
                        },
                        {
                            text: $t('Cancel'),
                            click: function() {
                                self.removeProfileCanceled();
                                this.closeModal();
                            }
                        }
                    ]
                });
            },
            removePaymentProfile: function (card, id) {
                var self = this;

                this.ajaxRequest(
                    self.options.deleteUrl,
                    {'hash' : id},
                    function (response) {
                        if (response.status == true) {
                            $(card).hide(1000, function () {
                                $(card).remove();
                            });
                            self.showSuccessMessage($t('Card was successfully removed'));
                        } else {
                            self.showErrorMessage($t('Issue during removing card'));
                        }
                    },
                    function (response) {
                        self.showErrorMessage(response);
                    }
                );
            },

            /** SYNC CUSTOMER PROFILE **/
            syncProfile: function() {
                var self = this;
                $(document).on('click', this.options.profileIdButton, function () {
                    self.syncCustomerProfile();
                });
            },
            syncCustomerProfile: function () {
                var self = this;
                var profileId = $(self.options.profileIdInput).val().trim();

                if (profileId == '' || profileId == 0) {
                    this.removeCustomerProfile();
                } else {
                    this.syncCustomerProfileAjax(profileId);
                }
            },
            removeCustomerProfile: function () {
                var self = this;
                modal({
                    title: 'Warning!',
                    content: $t('Do you want to remove customer profile?'),
                    closeOnEscape: false,
                    clickableOverlay: false,
                    buttons: [
                        {
                            text: $t('Yes, remove'),
                            click: function() {
                                self.syncCustomerProfileAjax(0);
                                this.closeModal();
                            }
                        },
                        {
                            text: $t('Cancel'),
                            click: function() {
                                this.closeModal();
                            }
                        }
                    ]
                });
            },
            syncCustomerProfileAjax: function (profileId) {
                var self = this;
                var customerId = $('#iwd_customer_id').val();

                this.ajaxRequest(
                    self.options.syncUrl,
                    {'profile_id' : profileId, 'customer_id' : customerId},
                    function (response) {
                        if (response.status == true) {
                            $('ul.profile-list').replaceWith(response.list_html);
                            self.hideProfileForm();
                            self.clearProfileForm();
                            self.showSuccessMessage($t('Customer profile in Magento was successfully synchronized with Authorize.net'));
                        } else {
                            self.showErrorMessage($t('Customer profile in Magento was not synchronized with Authorize.net'));
                        }
                    },
                    function (response) {
                        self.showErrorMessage(response);
                    }
                );
            },

            /**** AJAX REQUEST ****/
            ajaxRequest: function (url, data, successHandler, errorHandler) {
                var self = this;
                if (typeof FORM_KEY != "undefined") {
                    data.form_key = FORM_KEY;
                }

                $.ajax({
                    url: url,
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    beforeSend: function() {
                        self.showPreLoader();
                        self.hideErrorMessage();
                    }
                })
                    .done(function(response) {
                        if (response.error){
                            errorHandler(response);
                        } else {
                            successHandler(response);
                        }
                        self.hidePreLoader();
                    })
                    .fail(function(error) {
                        errorHandler(error);
                        self.hidePreLoader();
                    });
            },

            /**** ACCEPT JS ****/
            initAcceptJs: function () {
                if (!this.options.acceptEnabled) {
                    return;
                }


            },

            /**** HELPERS ****/
            initOptions: function(options) {
                var self = this;
                options = options || {};
                $.each(options, function(i, e){self.options[i] = e;});
            },
            showPreLoader: function() {
                $('.ajax-pre-loader').addClass('active');
            },
            hidePreLoader: function() {
                $('.ajax-pre-loader').removeClass('active');
            },
            showErrorMessage: function (response) {
                var message = '';
                if (typeof(response) != 'string') {
                    if (typeof(response.error_message) != 'undefined') {
                        message = response.error_message;
                    } else if (typeof(response.message) != 'undefined') {
                        message = response.message;
                    }
                } else {
                    message = response;
                }

                $("#iwd-authcim-messages .message").attr('class', 'message').addClass('error').addClass('message-error');
                $("#iwd-authcim-messages .message > div").html(message);
                $("#iwd-authcim-messages").show();
            },
            showSuccessMessage: function (message) {
                $("#iwd-authcim-messages .message").attr('class', 'message').addClass('success').addClass('message-success');
                $("#iwd-authcim-messages .message > div").html(message);
                $("#iwd-authcim-messages").show();
            },
            hideErrorMessage: function (message) {
                $("#iwd-authcim-messages").hide();
            },
            getCardItem: function (elem) {
                return $(elem).closest(this.options.cardListItem);
            },
            getCardItemId: function (elem) {
                return this.getCardItem(elem).data('id');
            },
            unselectAllCards: function () {
                $(this.options.cardListItem).removeClass('ui-state-active');
                $(this.options.actionSaveEditCc).hide();
                $(this.options.actionCancelEditCc).hide();
                $(this.options.actionEditCc).show();
            },
            getFormData: function(form){
                var unindexedArray = $(form).serializeArray();
                var indexedArray = {};

                $.map(unindexedArray, function(n){
                    indexedArray[n['name']] = n['value'];
                });

                return indexedArray;
            }
        });

        return $.mage.iwdAuthCimCustomerCards;
    });