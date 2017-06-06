/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'jquery/ui'
], function($){
    'use strict';

    $.widget("awrma.awRmaCustomFieldTypeSelect", {
        options: {
        },
        _create: function() {
            this._bind();
            this.selectChange();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.element).on('change', $.proxy(this.selectChange, this));
        },
        _unbind: function() {
            $(this.element).off('change');
        },
        selectChange: function() {
            var relatedInputs = $('[data-visible=' + $(this.element).prop('name') + ']');
            if ($.inArray($(this.element).val(), this.options.typesWithOptions) != -1) {
                this._showInput(relatedInputs);
            } else {
                this._hideInput(relatedInputs);
            }
        },
        _showInput: function(element) {
            element.removeClass('ignore-validate').show();
            element.closest('fieldset').show();
        },
        _hideInput: function(element) {
            element.addClass('ignore-validate').hide();
            element.closest('fieldset').hide();
        }
    });

    return $.awrma.awRmaCustomFieldTypeSelect;
});