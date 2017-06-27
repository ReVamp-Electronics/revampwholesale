/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'jquery/ui'
], function($){
    'use strict';

    $.widget("awrma.awRmaStatusFormCheckbox", {
        options: {
        },
        _create: function() {
            this._bind();
            this.checkboxClick();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('click', $.proxy(this.checkboxClick, this));
        },
        _unbind: function() {
            $(this.element).off('click');
        },
        checkboxClick: function() {
            var relatedInputs = $('[data-visible=' + $(this.element).prop('name') + ']');
            if ($(this.element).prop('checked')) {
                this._showInput(relatedInputs);
            } else {
                this._hideInput(relatedInputs);
            }
        },
        _showInput: function(element) {
            element.show();
            element.removeClass('ignore-validate');
        },
        _hideInput: function(element) {
            element.hide();
            element.addClass('ignore-validate');
        }
    });

    return $.awrma.awRmaStatusFormCheckbox;
});