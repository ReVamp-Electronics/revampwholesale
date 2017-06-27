/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'ko',
    'uiComponent',
    'Aheadworks_Rma/js/model/customer/request/view/address',
    'mage/mage'
], function($, ko, Component, address) {
    'use strict';

    return Component.extend({

        options: {
            formSelector: "#aw-rma-view-address",
            viewAddressSelector: "#contact-info-link"
        },

        editMode: ko.observable(false),
        isLoading: ko.observable(false),

        initialize: function () {
            address.init();
            this._bind();
        },
        _bind: function() {
            $(this.options.viewAddressSelector).on('click', $.proxy(this.onViewAddressClick, this));
        },
        onViewAddressClick: function(event) {
            $(this.options.formSelector).toggle();
            event.preventDefault();
        },
        editClick: function(data, event) {
            this.editMode(true);
        },
        getData: function(key) {
            return address.get(key, '');
        },
        formSubmit: function(form) {
            if ($(form).valid()) {
                return true;
            }
        }
    });
});
