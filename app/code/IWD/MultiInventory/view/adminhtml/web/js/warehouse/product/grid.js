define(
    ['jquery'],
    function ($) {
        'use strict';

        return {
            qtySelector: '.inventory_qty',
            qtyDefaultSelector: '.inventory_qty_default',
            inStockSelector: '.product_in_stock',
            gridSelector: '#iwd_catalog_stock_table',

            init: function () {
                this.focusInputs();
                this.keypressInputs();
                this.changeInputs();
                window.iwdWarehouseProductUpdateData = {};
            },

            focusInputs: function() {
                $(document).on('focus', this.qtySelector + ',' + this.qtyDefaultSelector, function(e){
                    $(this).attr('prev-value', $(this).val());
                    $(this).select();
                });
            },

            keypressInputs: function() {
                $(document).on('keypress', this.qtySelector + ',' + this.qtyDefaultSelector, function(e){
                    if (e.which == 13 || e.which == 8 || e.which == 0) {
                        return 1;
                    }
                    if ('+-*/'.indexOf(String.fromCharCode(e.which)) != -1) {
                        return $(this).val($(this).val());
                    }
                    return ('1234567890.+-*/'.indexOf(String.fromCharCode(e.which)) != -1);
                });
            },

            changeInputs: function() {
                var self = this;
                $(document).on('change', this.qtySelector, function(e){
                    var newQty = self.prepareNewQty(this);
                    $(this).val(newQty);

                    self.updateInStockCheckbox( this,newQty);
                    self.updateDefaultQty(this);
                    self.saveChangedInput(this);

                    $(this).attr('prev-value', newQty);
                });

                $(document).on('change', this.qtyDefaultSelector, function(e){
                    var newQty = self.prepareNewQty(this);
                    self.updateInStockCheckbox(this, newQty);
                    self.saveChangedInput(this);
                });

                $(document).on('change', this.inStockSelector, function(e){
                    self.saveChangedInput(this);
                });
            },

            prepareNewQty: function(item){
                var newQty = eval($(item).val());
                newQty = parseFloat(newQty);
                return (!newQty || isNaN(newQty)) ? 0 : newQty;
            },

            updateInStockCheckbox: function(item, newQty){
                var inStockCheckbox = $(item).closest('.product-stock-cell').find('input[type="checkbox"]');
                if (newQty > 0) {
                    $(inStockCheckbox).attr('checked', 'checked');
                } else {
                    $(inStockCheckbox).removeAttr('checked');
                }

                this.saveChangedInput(inStockCheckbox[0]);
            },

            updateDefaultQty: function(item) {
                var newQty = parseFloat($(item).val()); newQty = (!newQty || isNaN(newQty)) ? 0 : newQty;
                var oldQty = parseFloat($(item).attr('prev-value')); oldQty = (!oldQty || isNaN(oldQty)) ? 0 : oldQty;
                var defaultQtyInput = $(item).closest('tr').find('.inventory_qty_default');
                var defaultQty = parseFloat($(defaultQtyInput).val()); defaultQty = (!defaultQty || isNaN(defaultQty)) ? 0 : defaultQty;
                var qty = defaultQty + (newQty - oldQty);
                $(defaultQtyInput).val(qty);

                this.saveChangedInput(defaultQtyInput[0]);
                this.updateInStockCheckbox(defaultQtyInput, qty);
            },

            saveChangedInput: function(item) {
                var id = $(item).attr('name');
                if (typeof id != 'undefined') {
                    window.iwdWarehouseProductUpdateData[id] = item;
                }
            },

            iwdWarehouseProductUpdate: function(updateUrl) {
                if ($.isEmptyObject(window.iwdWarehouseProductUpdateData)) {
                    return;
                }

                var self = this;
                var showMessage = function(type) {
                    var message = $('#messages .message.' + type);
                    $(message).show();
                    setTimeout(function(){$(message).fadeOut(1500, "linear");}, 3000);
                };

                var inputs = $.map(window.iwdWarehouseProductUpdateData, function(value, i){return [value];});
                var data = $(inputs).serialize();
                $.each(inputs, function(i, item){if ($(item).attr('type') == 'checkbox' && $(item).prop( "checked") == false) {data += '&' + $(item).attr('name') + '=0';}});
                while(data.charAt(0)==='&'){data = data.substr(1);}

                $.ajax({
                    url: updateUrl,
                    data: data,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true
                }).done(function (result) {
                    if (result.status) {
                        showMessage('success');
                        window.iwdWarehouseProductUpdateData = {};
                    } else {
                        showMessage('error');
                        console.log(result.error);
                    }
                }).fail(function() {
                    showMessage('error');
                });
            },

            updateGrid: function()
            {
                var self = this;
                $.each(window.iwdWarehouseProductUpdateData, function(name, item){
                    var type = $(item).attr('type');
                    if (type == 'text') {
                        $(self.gridSelector).find('input[name="' + name + '"]').val($(item).val());
                    } else if (type == 'checkbox'){
                        $(self.gridSelector).find('input[name="' + name + '"]').attr('checked', $(item).attr('checked'));
                    }
                });
            }
        };
    }
);
