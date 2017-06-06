/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'jquery/file-uploader'
], function($, mageTemplate){
    'use strict';

    $.widget("awrma.awRmaFileUpload", {
        itemsCount: 0,
        itemIndex: 0,
        linkTitle: '',
        options: {
        },
        _create: function() {
            this.itemTemplate = mageTemplate(this.options.itemTemplate);
            this.linkTitle = $(this.options.addLink).html();
            this.element.fileupload({
                dataType: 'json',
                done: $.proxy(this.onUpload, this)
            });
            this._bind();
        },
        destroy: function() {
            this.element.fileupload('destroy');
            this._unbind();
        },
        _bind: function() {
            $(this.options.itemsContainer).on('click', this.options.removeLinks, $.proxy(this.onRemoveClick, this));
        },
        _unbind: function() {
            $(this.options.itemsContainer).off('click', this.options.removeLinks);
        },
        onUpload: function(e, data) {
            if (typeof data['result'] !== "undefined") {
                var result = data['result'];

                if (!result['error']) {
                    this.addItem(data['result']);
                } else {
                    this.showError(result['error']);
                }
            }
        },
        onRemoveClick: function(event) {
            var item = $(event.target).closest('li');
            if (item) {
                this.removeItem(item);
            }
            event.preventDefault();
        },
        addItem: function(data) {
            var templateData = {
                'index': this.itemIndex++,
                'file': data.file,
                'fileName': data.name,
                'fileSize': data.text_file_size
            };
            $(this.options.itemsContainer).append(this.itemTemplate(templateData));
            this.itemsCount++;
            this.switchLinkTitle();
            this.updateItemsContainerVisibility();
        },
        removeItem: function(item) {
            item.hide();
            item.find('[data-role=remove]').val(1);
            this.itemsCount--;
            this.switchLinkTitle();
            this.updateItemsContainerVisibility();
        },
        switchLinkTitle: function() {
            var addLink = $(this.options.addLink);
            if (this.itemsCount > 0) {
                addLink.html(addLink.data('switch-title'));
            } else {
                addLink.html(this.linkTitle);
            }
        },
        updateItemsContainerVisibility: function() {
            var itemsContainer = $(this.options.itemsContainer);
            if (this.itemsCount > 0) {
                itemsContainer.show();
            } else {
                itemsContainer.hide();
            }
        },
        showError: function(message) {
            $(this.options.errorContainer)
                .html(message)
                .fadeIn()
                .delay(1000)
                .fadeOut();
        }
    });

    return $.awrma.awRmaFileUpload;
});