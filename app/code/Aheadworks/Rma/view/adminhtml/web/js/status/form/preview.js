/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'jquery/ui'
], function($){
    'use strict';

    $.widget("awrma.awRmaStatusFormPreview", {
        options: {
        },
        _create: function() {
            this._bind();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('click', $.proxy(this.previewClick, this));
        },
        _unbind: function() {
            $(this.element).off('click');
        },
        previewClick: function(event) {
            var previewUrl = this.options.url;
            $.ajax({
                url: previewUrl,
                data: {
                    'form_key': FORM_KEY,
                    'template': $(this.options.template).val(),
                    'status': $(this.options.status).val(),
                    'to_admin': this.options.toAdmin
                },
                context: $('body'),
                showLoader: true
            }).done(function(data){
                window.open(previewUrl, '_blank', 'resizable, scrollbars, status, top=0, left=0, width=600, height=500');
            });
            event.preventDefault();
        }
    });

    return $.awrma.awRmaStatusFormPreview;
});