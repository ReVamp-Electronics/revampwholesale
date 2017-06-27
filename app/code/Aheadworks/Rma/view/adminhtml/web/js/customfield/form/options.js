/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery',
    'mage/template',
    'uiRegistry',
    'jquery/ui'
], function ($, mageTemplate) {
    'use strict';

    $.widget("awrma.awRmaCustomFieldOptions", {
        optionDefaultInputType: 'radio',
        itemCount: 0,
        totalItems: 0,
        rendered: 0,
        options: {
        },
        _create: function() {
            var self = this;
            this.table = $(this.options.tableSelector);
            this.template = mageTemplate(this.options.rowSelector);
            $.each(this.options.optionValues, function() {
                self.add(this);
            });
            this.initSortable();
            this._bind();
        },
        destroy: function() {
            this._unbind();
        },
        _bind: function() {
            $(this.options.addBtnSelector).on('click', $.proxy(this.addClick, this));
            $('[data-role=options-container]').on('click', this.options.deleteBtnSelector, $.proxy(this.remove, this));
        },
        _unbind: function() {
            $(this.options.addBtnSelector).off('click');
            $('[data-role=options-container]').off('click');
        },
        initSortable: function() {
            $('[data-role=options-container]').sortable({
                distance: 8,
                tolerance: 'pointer',
                cancel: 'input, button',
                axis: 'y',
                update: function () {
                    $('[data-role=options-container] [data-role=order]').each(function (index, element) {
                        $(element).val(index + 1);
                    });
                }
            });
        },
        addClick: function() {
            this.add({});
        },
        add: function (data) {
            var isNewOption = false;
            if (typeof data.id == 'undefined') {
                data = {
                    'id': 'option_' + this.itemCount,
                    'sort_order': this.itemCount + 1
                };
                isNewOption = true;
            }
            if (!data.intype) {
                data.intype = this.optionDefaultInputType;
            }
            if (isNewOption) {
                data.enable = 'checked';
                if (!this.totalItems) {
                    data.checked = 'checked';
                }
            }

            this.itemCount++;
            this.totalItems++;
            this.elements += this.template({data: data});

            this.render();

            if (isNewOption) {
                this.enableNewOptionDeleteButton(data.id);
            }
        },
        remove: function (event) {
            var element = $(event.target).closest('tr');
            if (element) {
                element.find('.delete-flag').val(1);
                element.addClass('no-display').addClass('template');
                element.hide();
                this.totalItems--;
                this.updateItemsCountField();
            }
        },
        render: function () {
            $('[data-role=options-container]').append(this.elements);
            this.elements = '';
        },
        enableNewOptionDeleteButton: function (id) {
            $('#delete_button_container_' + id + ' button').each(function () {
                $(this).show().removeClass('disabled');
            });
        },
        updateItemsCountField: function () {
            $('option-count-check').value = this.totalItems > 0 ? '1' : '';
        }
    });

    return $.awrma.awRmaCustomFieldOptions;
});
