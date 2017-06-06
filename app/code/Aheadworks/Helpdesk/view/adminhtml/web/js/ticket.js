/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    "jquery",
    "loadingPopup"
], function($){
    'use strict';

    $.awHelpdeskTicketManager = {
        init: function(config) {
            this.orderExternalLink = $(config.externalLinkSelector);
            this.orderStatus = $(config.orderStatusSelector);
            this.orderCreated = $(config.orderCreatedSelector);

            this.orderItemsContainer = $(config.orderItemsContainerSelector);
            this.orderStatusContainer = $(config.orderStatusContainerSelector);
            this.orderCreatedContainer = $(config.orderCreatedContainerSelector);
        },
        changeOrder: function(url, orderId) {
            var me = this;
            $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                async: true,
                context: this,
                data: {
                    isAjax: 'true',
                    order_id: orderId
                },
                complete: function(response) {
                    try {
                        var json = jQuery.parseJSON(response.responseText) || {};
                    } catch (e) {
                        return;
                    }

                    if (json.success) {
                        me.orderExternalLink.attr('href', json.external_link);
                        me.orderStatus.html(json.status);
                        me.orderCreated.html(json.created_at);
                        me.orderItemsContainer.html(json.order_items);

                        //show content if hided.
                        me.orderExternalLink.show();
                        me.orderStatusContainer.show();
                        me.orderCreatedContainer.show();
                        me.orderItemsContainer.show();
                    } else {
                        me.orderExternalLink.hide();
                        me.orderStatusContainer.hide();
                        me.orderCreatedContainer.hide();
                        me.orderItemsContainer.hide();
                    }
                }
            });
        }
    };

    return $.awHelpdeskTicketManager;
});