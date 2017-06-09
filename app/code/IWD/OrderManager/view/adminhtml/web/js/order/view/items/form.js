define([
        "jquery",
        "jquery/ui",
        "Magento_Catalog/catalog/product/composite/configure"
    ],

function ($) {
    'use strict';

    $.widget('mage.iwdOrderManagerItemsForm', {
        options: {
            classInputChanges: 'input.edit_order_item',
            classInputValidate: 'input.validate-number',
            classCheckboxRemove: 'input[type=checkbox].remove_ordered_item',

            taxCalculationMethodBasedOn: 0, /* algorithm */
            taxCalculationBasedOn: 0,
            catalogPrices: 0, /* 1-include; 0-exclude tax */
            shippingPrices: 0,
            applyTaxAfterDiscount: 0, /* applyCustomerTax */
            discountTax: 0, /* applyDiscountOnPrices */
            validateStockQty: 1,

            configureQuoteItemsUrl: '',
            configureConfirmUrl: ''
        },

        productConfigureAddFields: {},
        productQty: {},

        CALC_TAX_BEFORE_DISCOUNT_ON_EXCL: '0_0',
        CALC_TAX_BEFORE_DISCOUNT_ON_INCL: '0_1',
        CALC_TAX_AFTER_DISCOUNT_ON_EXCL: '1_0',
        CALC_TAX_AFTER_DISCOUNT_ON_INCL: '1_1',

        init: function(options){
            this._initOptions(options);

            this.validateInputs();
            this.onChangeInput();
            this.onChangeCheckbox();
            this.onClickConfigureButton();
            window.qtyWarning = 0;
        },

        _initOptions: function(options) {
            var self = this;
            options = options || {};
            $.each(options, function(i, e){self.options[i] = e;});
        },

        validateInputs:function() {
            $(this.options.classInputValidate).on('keypress', function (e) {
                if (e.which == 13 || e.which == 8) return 1;
                var letters = '1234567890.,+-';
                return (letters.indexOf(String.fromCharCode(e.which)) != -1);
            });
        },

        onChangeInput:function() {
            var self = this;
            $(document).on('change', self.options.classInputChanges, function(){
                self.updateOrderItemInput(this);
            });
        },

        readProductStockQty:function(input){
            this.productQty[input.product_id] = parseFloat($(input.fact_qty).attr("data-stock-qty"));
        },

        /* 1. After every change */
        updateOrderItemInput:function (item) {
            var id = this.getInputId(item);
            var name = this.getInputName(item);
            var input = this.getInputs(id);

            this.readProductStockQty(input);

            switch(name){
                case "price":
                    this._calculatePriceExclTax(input);
                    this._calculateSubtotal(input);
                    break;
                case "price_incl_tax":
                    this._calculatePriceInclTax(input);
                    this._calculateSubtotal(input);
                    break;
                case "fact_qty":
                    this._checkFactQty(input);
                    this._calculateSubtotal(input);
                    break;
                case "tax_amount":
                    break;
                case "tax_percent":
                    this._changePrice(input);
                    this._calculateSubtotal(input);
                    break;
                case "discount_amount":
                    this._checkDiscountAmount(input);
                    break;
                case "discount_percent":
                    this._checkDiscountAmount(input);
                    break;
            }

            this.baseCalculation(input);
            this._calculateRowTotal(input);

            this.updateBundleProduct(input, name, id);

            /* item is a part of bundle product (has parent) */
            if (input.parent.val()) {
                var parentId = input.parent.val();
                var bundleItems = this.getBundleItems(parentId);
                this.calculateBundleTotals(bundleItems, parentId);
            }
        },

        updateBundleProduct: function (input, name, id) {
            var bundleItems = this.getBundleItems(id);
            if (Object.keys(bundleItems).length == 0){
                return;
            }

            if (name == "fact_qty") {
                var bundle = this.getInputs(id);
                var bundleQty = parseFloat(bundle.fact_qty.val());
                var self = this;

                $.each(bundleItems, function (i, input) {
                    var qtyItemInBundle = parseFloat(input.qty_item_in_bundle.val());
                    input.fact_qty.val(bundleQty * qtyItemInBundle).change();
                    self.updateQtyInBundle(input, bundle);
                });
            }
        },

        getInputId: function (item) {
            var reg = /item\[(\w+)\]\[(\w+)\]/i;
            var attr_name = reg.exec($(item).attr('name'));
            return attr_name[1];
        },
        getInputName: function (item) {
            var reg = /item\[(\w+)\]\[(\w+)\]/i;
            var attr_name = reg.exec($(item).attr('name'));
            return attr_name[2];
        },
        getInputs: function (id) {
            var fields = [
                'price',
                'price_incl_tax',
                'subtotal',
                'subtotal_incl_tax',
                'tax_amount',
                'weee_tax_applied_row_amount',
                'discount_tax_compensation_amount',
                'tax_percent',
                'discount_amount',
                'discount_percent',
                'row_total',
                'qty_item_in_bundle',
                'fact_qty',
                'remove',
                'parent',
                'product_id'
            ];

            var inputs = {
                "item_id" : id
            };

            $.each(fields, function(i, name){
                inputs[name] = $("input[name='item[" + id + "]["+ name +"]']");
            });

            return inputs;
        },

        _calculatePriceExclTax: function (input) {
            var price_excl_tax = this.evalItem(input.price, 0.0);
            var tax_percent = this.evalItem(input.tax_percent, 0.0);

            var price = price_excl_tax * (1 + tax_percent / 100);

            input.price.val(price_excl_tax.toFixed(2));
            input.price_incl_tax.val(price.toFixed(2));
            input.tax_percent.val(tax_percent.toFixed(2));
        },
        _calculatePriceInclTax: function (input) {
            var price_incl_tax = this.evalItem(input.price_incl_tax, 0.0);
            var tax_percent = this.evalItem(input.tax_percent, 0.0);

            var price = price_incl_tax / (1 + tax_percent / 100);

            input.price.val(price.toFixed(2));
            input.price_incl_tax.val(price_incl_tax.toFixed(2));
            input.tax_percent.val(tax_percent.toFixed(2));
        },

        getBundleItems: function (bundle_id) {
            var children = {};
            var self = this;
            $(".has-parent-" + bundle_id).each(function(){
                var item_id = $(this).attr('data-item-id');
                if (item_id != bundle_id){
                    children[item_id] = self.getInputs(item_id);
                }
            });
            return children;
        },

        getCalculationSequence: function () {
            if (this.options.applyTaxAfterDiscount) {
                return (this.options.discountTax)
                    ? this.CALC_TAX_AFTER_DISCOUNT_ON_INCL
                    : this.CALC_TAX_AFTER_DISCOUNT_ON_EXCL;
            } else {
                return (this.options.discountTax)
                    ? this.CALC_TAX_BEFORE_DISCOUNT_ON_INCL
                    : this.CALC_TAX_BEFORE_DISCOUNT_ON_EXCL;
            }
        },

        enabledSubmitButton: function () {
            $('#edit_ordered_items_submit').removeAttr('disabled').removeClass('disabled');
        },

        _checkFactQty: function(item){
            var data_stock_validate = $(item.fact_qty).attr("data-stock-validate");
            if(data_stock_validate == 0){
                return;
            }

            var data_stock_qty_increment = parseFloat($(item.fact_qty).attr("data-stock-qty-increment"));
            var data_stock_qty = parseFloat($(item.fact_qty).attr("data-stock-qty"));
            var data_stock_min_sales_qty = parseFloat($(item.fact_qty).attr("data-stock-min-sales-qty"));
            var data_stock_max_sales_qty = parseFloat($(item.fact_qty).attr("data-stock-max-sales-qty"));

            var qty_value = this.evalItem(item.fact_qty, 1);
            if(qty_value <= 0){qty_value = 1;}

            this.productQty[item.item_id] = data_stock_qty;

            if(this.options.validateStockQty == 1){
                /* check max sales qty */
                if (qty_value > data_stock_max_sales_qty) {
                    qty_value = data_stock_max_sales_qty;
                }

                /* check min sales qty */
                if (qty_value < data_stock_min_sales_qty) {
                    qty_value = data_stock_min_sales_qty;
                }
            }

            /* check qty increment */
            if(qty_value % data_stock_qty_increment != 0){
                qty_value = Math.round((qty_value / data_stock_qty_increment)) * data_stock_qty_increment;
            }

            /* check stock qty */
            if(data_stock_qty < qty_value && data_stock_qty  > 0){
                if(this.options.validateStockQty == 1){
                    qty_value = data_stock_qty;
                }
                $('.notice_' + item.item_id).show();
                $('.notice_' + item.item_id + ' .notice_qty').show();
                window.qtyWarning += 1;
            } else {
                $('.notice_' + item.item_id).hide();
                $('.notice_' + item.item_id + ' .notice_qty').hide();
                window.qtyWarning -= 1;
            }

            $(item.fact_qty).val(qty_value);
        },

        updateQtyInBundle: function(item, parent){
            var item_qty = this.evalItem(item.fact_qty, 1);
            var parent_qty = this.evalItem(parent.fact_qty, 1);

            var qty_in_bundle = item_qty / parent_qty;
            qty_in_bundle = qty_in_bundle != qty_in_bundle.toFixed(2) ? qty_in_bundle.toFixed(2) : qty_in_bundle;
            $("#qty_in_bundle_" + item.item_id).text(qty_in_bundle);
        },

        calculateBundleTotals: function (bundle_items, bundle_id) {
            /* !canShowPriceInfo */
            if (!bundle_items[Object.keys(bundle_items)[0]].price.val()){
                return false;
            }

            var total_price_tax_incl = 0;
            var total_price_tax_excl = 0;
            var total_subtotal_tax_incl = 0;
            var total_subtotal_tax_excl = 0;
            var total_tax_amount = 0;
            var bundle = this.getInputs(bundle_id);
            var self = this;

            var bundle_qty = parseFloat(bundle.fact_qty.val());
            $.each(bundle_items, function (i, input) {
                /* item was removed */
                if (input.remove.prop("checked")) {
                    return true;
                }
                var qty = parseFloat(input.fact_qty.val()) / bundle_qty;
                total_price_tax_incl += parseFloat(input.price_incl_tax.val()) * qty;
                total_price_tax_excl += parseFloat(input.price.val()) * qty;
                total_subtotal_tax_incl += parseFloat(input.subtotal_incl_tax.val());
                total_subtotal_tax_excl += parseFloat(input.subtotal.val());
                total_tax_amount += parseFloat(input.tax_amount.val());

                self.updateQtyInBundle(input, bundle);
            });

            bundle.price_incl_tax.val(total_price_tax_incl.toFixed(2));
            bundle.price.val(total_price_tax_excl.toFixed(2));
            bundle.subtotal_incl_tax.val(total_subtotal_tax_incl.toFixed(2));
            bundle.subtotal.val(total_subtotal_tax_excl.toFixed(2));
            bundle.tax_amount.val(total_tax_amount.toFixed(2));

            return true;
        },

        evalItem: function(item, defaultVal){
            var val = $(item).val();
            try{val = eval(val);}catch(e){}
            val = parseFloat(val);
            if(isNaN(val)){return defaultVal;}
            return val;
        },

        /* 2. Select a tax calculation method */
        baseCalculation: function (input) {
            switch (this.options.taxCalculationMethodBasedOn) {
                case 'UNIT_BASE_CALCULATION':
                    this._unitBaseCalculation(input);
                    break;
                case 'ROW_BASE_CALCULATION':
                    this._rowBaseCalculation(input);
                    break;
                case 'TOTAL_BASE_CALCULATION':
                    this._totalBaseCalculation(input);
                    break;
            }
        },

        /* 2.1. Method: Unit price */
        _unitBaseCalculation: function (input) {
            var tax_amount = 0;
            var discount_tax_compensation_amount = 0;
            var unitTaxDiscount = 0;
            var unitTax = 0;
            var qty;
            var discountAmount;
            var price;
            var discountRate;

            switch (this.getCalculationSequence()) {
                case this.CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
                    tax_amount = this._calcTaxAmount(input.subtotal.val(), input.tax_percent.val(), 0);
                    this._calculateDiscountAmount(input, input.subtotal.val());
                    break;
                case this.CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                    tax_amount = this._calcTaxAmount(input.subtotal_incl_tax.val(), input.tax_percent.val(), 1);
                    this._calculateDiscountAmount(input, input.subtotal_incl_tax.val());
                    break;

                case this.CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
                    this._calculateDiscountAmount(input, input.subtotal.val());

                    qty = parseFloat(input.fact_qty.val());
                    discountAmount = parseFloat(input.discount_amount.val()) / qty;
                    price = parseFloat(input.price_incl_tax.val());

                    if (this.options.catalogPrices) {
                        unitTax = this._calcTaxAmount(price, input.tax_percent.val(), 1);
                        discountRate = (unitTax / price) * 100;
                        unitTaxDiscount = this._calcTaxAmount(discountAmount, discountRate, 0);  /*1*/
                        discount_tax_compensation_amount = this._calcTaxAmount(discountAmount, input.tax_percent.val(), 1);
                    } else {
                        price = parseFloat(input.price.val());
                        unitTax = this._calcTaxAmount(price, input.tax_percent.val(), 0);
                        unitTaxDiscount = this._calcTaxAmount(discountAmount, input.tax_percent.val(), 0);
                    }

                    unitTax = Math.max(unitTax - unitTaxDiscount, 0);
                    tax_amount = Math.max(qty * unitTax, 0);
                    discount_tax_compensation_amount = Math.max(qty * discount_tax_compensation_amount, 0);
                    break;

                case this.CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                    this._calculateDiscountAmount(input, input.subtotal_incl_tax.val());

                    qty = parseFloat(input.fact_qty.val());
                    discountAmount = parseFloat(input.discount_amount.val()) / qty;
                    price = parseFloat(input.price_incl_tax.val());

                    if (this.options.catalogPrices) {
                        unitTax = this._calcTaxAmount(price, input.tax_percent.val(), 1);
                        discountRate = (unitTax / price) * 100;
                        unitTaxDiscount = this._calcTaxAmount(discountAmount, discountRate, 0); /*1*/
                        discount_tax_compensation_amount = this._calcTaxAmount(discountAmount, input.tax_percent.val(), 1);
                    } else {
                        price = parseFloat(input.price.val());
                        unitTax = this._calcTaxAmount(price, input.tax_percent.val(), 0);
                        unitTaxDiscount = this._calcTaxAmount(discountAmount, input.tax_percent.val(), 0);
                    }

                    unitTax = Math.max(unitTax - unitTaxDiscount, 0);
                    tax_amount = Math.max(qty * unitTax, 0);
                    discount_tax_compensation_amount = Math.max(qty * discount_tax_compensation_amount, 0);
                    break;
            }

            input.tax_amount.val(tax_amount.toFixed(2));
            input.discount_tax_compensation_amount.val(discount_tax_compensation_amount.toFixed(2));
        },

        /* 2.2. Method: Row total */
        _rowBaseCalculation: function (input) {
            var tax_amount = 0;
            var discount_tax_compensation_amount = 0;
            switch (this.getCalculationSequence()) {
                case this.CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
                    tax_amount = this._calcTaxAmount(input.subtotal.val(), input.tax_percent.val(), 0);
                    this._calculateDiscountAmount(input, input.subtotal.val());
                    break;

                case this.CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                    tax_amount = this._calcTaxAmount(input.subtotal_incl_tax.val(), input.tax_percent.val(), 1);
                    this._calculateDiscountAmount(input, input.subtotal_incl_tax.val());
                    break;

                case this.CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
                    this._calculateDiscountAmount(input, input.subtotal.val());
                    if (this.options.catalogPrices) {
                        discount_tax_compensation_amount = this._calcTaxAmount(input.discount_amount.val(), input.tax_percent.val(), 1);
                        tax_amount = this._calcTaxAmount(input.subtotal.val(), input.tax_percent.val(), 0);
                        tax_amount -= discount_tax_compensation_amount;
                    } else {
                        tax_amount = this._calcTaxAmount(input.subtotal.val() - input.discount_amount.val(), input.tax_percent.val(), 0);
                    }
                    break;

                case this.CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                    this._calculateDiscountAmount(input, input.subtotal_incl_tax.val());
                    if (this.options.catalogPrices) {
                        discount_tax_compensation_amount = this._calcTaxAmount(input.discount_amount.val(), input.tax_percent.val(), 1);
                        tax_amount = this._calcTaxAmount(input.subtotal.val(), input.tax_percent.val(), 0);
                        tax_amount -= discount_tax_compensation_amount;
                    } else {
                        tax_amount = this._calcTaxAmount(input.subtotal.val() - input.discount_amount.val(), input.tax_percent.val(), 0);
                    }
                    break;
            }

            input.tax_amount.val(tax_amount.toFixed(2));
            input.discount_tax_compensation_amount.val(discount_tax_compensation_amount.toFixed(2));
        },

        /* 2.3. Method: Total */
        _totalBaseCalculation: function (input) {
            var tax_amount = 0;
            var price = 0;
            var discount_tax_compensation_amount = 0;

            switch (this.getCalculationSequence()) {
                case this.CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
                    tax_amount = this._calcTaxAmount(input.subtotal.val(), input.tax_percent.val(), 0);
                    this._calculateDiscountAmount(input, input.subtotal.val());
                    break;

                case this.CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                    tax_amount = this._calcTaxAmount(input.subtotal_incl_tax.val(), input.tax_percent.val(), 1);
                    this._calculateDiscountAmount(input, input.subtotal_incl_tax.val());
                    break;

                case this.CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
                    this._calculateDiscountAmount(input, input.subtotal.val());
                    discount_tax_compensation_amount = this._calcTaxAmount(input.discount_amount.val(), input.tax_percent.val(), 0);

                    price = input.subtotal_incl_tax.val() - input.discount_amount.val();
                    if(!this.options.catalogPrices){
                        price -= discount_tax_compensation_amount;
                        discount_tax_compensation_amount = 0;
                    }

                    tax_amount = this._calcTaxAmount(price, input.tax_percent.val(), 1);
                    break;

                case this.CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                    this._calculateDiscountAmount(input, input.subtotal_incl_tax.val());

                    price = input.subtotal.val() - input.discount_amount.val();
                    if(this.options.catalogPrices) {
                        discount_tax_compensation_amount = this._calcTaxAmount(input.discount_amount.val(), input.tax_percent.val(), 1);
                        price += discount_tax_compensation_amount;
                    } else {
                        discount_tax_compensation_amount = 0;
                    }

                    tax_amount = this._calcTaxAmount(price, input.tax_percent.val(), 0);
                    break;
            }

            input.tax_amount.val(tax_amount.toFixed(2));
            input.discount_tax_compensation_amount.val(discount_tax_compensation_amount.toFixed(2));
        },


        _calculateDiscountAmount: function (input, subtotal) {
            var discount_percent = parseFloat(input.discount_percent.val());
            var discount_amount = 0;

            if(discount_percent != 0) {
                discount_amount = subtotal * discount_percent / 100;
            } else {
                discount_amount = parseFloat(input.discount_amount.attr('value'));
                discount_percent = 0;
            }

            input.discount_amount.val(discount_amount.toFixed(2));
            input.discount_percent.val(discount_percent.toFixed(2));
        },

        _calcTaxAmount: function (price, tax_percent, priceIncludeTax) {
            var tax_rate = parseFloat(tax_percent) / 100;
            price = parseFloat(price);

            if (priceIncludeTax) {
                return price * (1 - 1 / (1 + tax_rate));
            } else {
                return price * tax_rate;
            }
        },

        _calculateSubtotal: function (input) {
            var subtotal = parseFloat(input.price.val()) * parseFloat(input.fact_qty.val());
            var subtotal_incl_tax = parseFloat(input.price_incl_tax.val()) * parseFloat(input.fact_qty.val());
            input.subtotal.val(subtotal.toFixed(2));
            input.subtotal_incl_tax.val(subtotal_incl_tax.toFixed(2));
        },

        _calculateRowTotal: function (input) {
            var subtotal = parseFloat(input.subtotal.val());
            var discount_amount = parseFloat(input.discount_amount.val());
            var tax_amount = parseFloat(input.tax_amount.val());
            var discount_tax_compensation_amount = parseFloat(input.discount_tax_compensation_amount.val());
            var weee_tax_applied_row_amount = parseFloat(input.weee_tax_applied_row_amount.val());

            var row_total = subtotal + tax_amount + discount_tax_compensation_amount + weee_tax_applied_row_amount - discount_amount;

            input.row_total.val(row_total.toFixed(2));
            return row_total;
        },

        _changePrice: function (input) {
            if (this.options.catalogPrices) {
                this._calculatePriceInclTax(input); /* incl tax fixed */
            } else {
                this._calculatePriceExclTax(input); /* excl tax fixed */
            }
        },

        _checkDiscountAmount: function(input) {
            var discount_percent = this.evalItem(input.discount_percent, 0.0);
            var discount_amount = parseFloat(input.discount_amount.val());

            if(isNaN(discount_percent) || isNaN(discount_amount) || !discount_percent) {
                input.discount_percent.val(0.0);
                input.discount_amount.val(0.0);
            }

            $(input.discount_percent).val(discount_percent);
        },


        onChangeCheckbox:function() {
            var self = this;
            $(self.options.classCheckboxRemove).on('change', function () {
                self.removeItemRow(this);
            });

            $('.action-multicheck-toggle').on('click', function () {
                if($(this).hasClass('_active')){
                    $(this).removeClass('_active');
                    $(this).closest('.action-multicheck-wrap').removeClass('_active');
                } else {
                    $(this).addClass('_active');
                    $(this).closest('.action-multicheck-wrap').addClass('_active');
                }
            });
        },

        removeItemRow: function (item) {
            var parent_id = $(item).attr('data-parent-id') || null;
            var id = $(item).attr('data-item-id') || null;
            var result = true;

            if ($(item).prop("checked")) {
                result = this.disabledRow(id, parent_id);
            } else {
                result = this.enabledRow(id, parent_id);
            }

            if (parent_id && result) {
                var bundle_items = this.getBundleItems(parent_id);
                if (!this.isRemoveAllBundleItems(bundle_items, parent_id)){
                    this.calculateBundleTotals(bundle_items, parent_id);
                }
            }
        },

        onClickConfigureButton:function() {
            var self = this;
            $(document).off('click touchstart', '.configure-order-item');
            $(document).on('click touchstart', '.configure-order-item', function () {
                var id = $(this).data('order-item-id');
                self.showQuoteItemConfiguration(id);
            });
        },

        showQuoteItemConfiguration: function(itemId){
            productConfigure.blockMsgError.innerHTML = '';
            productConfigure.blockMsg.hide();
            var self = this;

            if (window.ProductConfigure) {
                productConfigure.addListType('order_items', {
                    urlFetch: self.options.configureQuoteItemsUrl,
                    urlConfirm: self.options.configureConfirmUrl
                });
            }

            var listType = 'order_items';
            var qtyElement = $('.iwd-om-edit-order-table input[name="item\['+itemId+'\]\[fact_qty\]"]')[0];

            productConfigure.setShowWindowCallback(listType, function(response) {
                var formCurrentQty = productConfigure.getCurrentFormQtyElement();
                if (formCurrentQty && qtyElement && !isNaN(qtyElement.value)) {
                    formCurrentQty.value = qtyElement.value;
                }
                $('.loading-mask').hide();
            }.bind(this));

            productConfigure.setOnLoadIFrameCallback(listType, function(response) {
                $('.loading-mask').hide();

                if (!response.ok) {
                    return;
                }
                var itemId = response.item_id;

                $('.item_name_' + itemId).html(response.name);
                $('.item_sku_' + itemId).html(response.sku);
                $('.item_options_' + itemId).html(response.options_html);
                $('input[name="item[' + itemId + '][price]"]').val(response.price).change();
                $('input[name="item[' + itemId + '][product_options]"]').val(response.product_options);
                $('input[name="item[' + itemId + '][sku]"]').val(response.sku);

                var confirmedCurrentQty = productConfigure.getCurrentFormQtyElement();
                if (qtyElement && confirmedCurrentQty && !isNaN(confirmedCurrentQty.value)) {

                    if(response.stock){
                        $.each(response.stock, function(data, val){
                            $(qtyElement).attr(data, val);
                        });
                    }

                    qtyElement.value = confirmedCurrentQty.value;
                    $(qtyElement).change();
                }
            }.bind(this));

            productConfigure.showItemConfiguration(listType, itemId);
        },

        disabledRow: function (row_id, parent_id) {
            var row_item = $('.iwd-om-edit-order-table tr[data-item-id="' + row_id + '"]');
            row_item.addClass('removed_item');
            row_item.find('input[type=text], button:not(.action-multicheck-toggle)').attr('disabled', 'disabled');
            row_item.find('button:not(.action-multicheck-toggle)').addClass('disabled');

            $('label[for="remove_'+row_id+'"]').addClass('checked');

            /* for bundle product */
            $('input.remove_ordered_item[data-parent-id="' + row_id + '"]').each(function(){
                $(this).prop("checked", true).click(this.deactivator);
                $('label[for="'+$(this).attr('id')+'"]').addClass('checked');
            });
            $('tr.has-parent-' + row_id).addClass('removed_item');
            $('tr.has-parent-' + row_id + ' input[type=text]').attr('disabled', 'disabled');

            return true;
        },

        enabledRow: function (row_id, parent_id) {
            if (parent_id && $('#remove_' + parent_id).prop("checked")){
                return false;
            }

            var row_item = $('.iwd-om-edit-order-table tr[data-item-id="' + row_id + '"]');
            row_item.removeClass('removed_item');
            row_item.find('input[type=text], button:not(.action-multicheck-toggle)').removeAttr('disabled');
            row_item.find('button:not(.action-multicheck-toggle)').removeClass('disabled');

            $('label[for="remove_'+row_id+'"]').removeClass('checked');

            /* for bundle product */
            $('input.remove_ordered_item[data-parent-id="' + row_id + '"]').each(function(){
                $(this).prop("checked", false).unbind('click', this.deactivator);
                $('label[for="'+$(this).attr('id')+'"]').removeClass('checked');
            });
            $('tr.has-parent-' + row_id).removeClass('removed_item');
            $('tr.has-parent-' + row_id + ' input[type=text]').removeAttr('disabled');

            return true;
        },

        isRemoveAllBundleItems: function (bundle_items, bundle_id) {
            var count_removed_items = 0;
            $.each(bundle_items, function (i, input) {
                if (input.remove.prop("checked")) count_removed_items++;
            });

            /* checked all bundle items */
            if (count_removed_items == Object.keys(bundle_items).length) {
                $('input.remove_ordered_item[data-parent-id="' + bundle_id + '"]').prop("checked", false);
                this.calculateBundleTotals(bundle_items, bundle_id);
                $('input[name="item[' + bundle_id + '][remove]"').prop("checked", true);
                this.disabledRow(bundle_id, null);
                return true;
            }

            return false;
        },

        deactivator: function (event) {
            event.preventDefault();
        }
    });

    return $.mage.iwdOrderManagerItemsForm;
});