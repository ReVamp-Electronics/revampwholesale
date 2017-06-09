define([
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'mage/template',
        'text!IWD_MultiInventory/templates/grid/cells/stock.html',
        'text!IWD_MultiInventory/templates/grid/cells/stock/modal.html',
        'Magento_Ui/js/modal/modal',
        'IWD_All/js/iwd/plugins/jquery.nicescroll.min',
        'jquery/ui'
    ],

    function ($, modalAlert, $t, mageTemplate, cellTemplate, modalTemplate, modal) {
        'use strict';

        return {
            options: {
                urlUpdate: '',
                urlForm: ''
            },
            row: {},
            stocksData: {},
            allOrderItems: {},
            orderId: 0,
            stockTablesClass: ".assign-stock-tables",
            modalClass: 'iwd-multi-stock-assign-modal',
            modal: null,
            initZippedForm: '',

            init: function (options) {
                this.options.urlUpdate = options.urlUpdate;
                this.options.urlForm = options.urlForm;

                var self = this;
                $(document).on('showMultistockModalEvent', function (event, row) {
                    self.preview(row);
                });
            },

            setRow: function (row) {
                this.row = row;
            },

            getUrlForm: function (row) {
                return this.options.urlForm == '' ? row.urlForm : this.options.urlForm;
            },

            getUrlUpdate: function (row) {
                return this.options.urlUpdate == '' ? row.urlUpdate : this.options.urlUpdate;
            },

            loadData: function (row) {
                var orderId = this.getId(row);
                var self = this;

                $.ajax({
                    url: self.getUrlForm(row),
                    data: {'order_id': orderId, 'form_key': FORM_KEY},
                    type: 'post',
                    dataType: 'json',
                    showLoader: true
                }).done(function (result) {
                    if(!result.allowed){
                        return self.showModal(result);
                    }

                    self.stocksData[orderId] = result;
                    self.preview(row);
                    self.initZippedForm = self.getZippedForm();
                });
            },

            setStockData: function (result) {
                var orderId = this.getId(result);
                this.stocksData[orderId] = result;
            },

            updateStocksData: function (row) {
                var self = this;
                var formData = $(this.stockTablesClass + " :input[value!='']").serialize();
                var orderId = this.getId(row);

                $.ajax({
                    url: self.getUrlUpdate(row),
                    data: 'order_id=' + orderId + '&' + formData,
                    type: 'post',
                    dataType: 'json',
                    showLoader: true
                }).done(function (result) {
                    self.updateStockCell(result);
                    self.modal.trigger('closeModal');
                });

                return true;
            },

            updateStockCell: function(){ /* rewrited on js/order/grid/columns/stock.js and js/order/view/warehouse.js */},

            getLabel: function (row) {
                return mageTemplate(
                    cellTemplate,
                    {
                        assignedQty: this.getAssignedQty(row),
                        notAssignedQty: this.getNotAssignedQty(row),
                        orderedQty: this.getOrderedQty(row),
                        refundedQty: this.getRefundedQty(row),
                        isOrderPlacesBefore: this.getIsOrderPlacesBefore(row),
                        isNotApplicable: this.getIsNotApplicable(row),
                        id: this.getId(row)
                    }
                );
            },

            getAssignedQty: function (row) {
                return row['assignedQty'] * 1;
            },

            getOrderedQty: function (row) {
                return row['orderedQty'] * 1;
            },

            getIsOrderPlacesBefore: function (row) {
                return row['isOrderPlacesBefore'] * 1;
            },

            getRefundedQty: function (row) {
                if (row['refundedQty']) {
                    return row['refundedQty'] * 1;
                }
                if (this.getNotAssignedQty(row) < 0) {
                    return this.getNotAssignedQty(row);
                }
                return 0;
            },

            getNotAssignedQty: function (row) {
                return this.getOrderedQty(row) - this.getAssignedQty(row);
            },

            getIsNotApplicable: function (row) {
                return row['isNotApplicable']
                    || (this.getAssignedQty(row) <= 0 && this.getNotAssignedQty(row) == 0);
            },

            getId: function (row) {
                return row['id']
            },

            preview: function (row) {
                var self = this;
                this.orderId = this.getId(row);

                if (this.getIsNotApplicable(row)) {
                    return;
                }

                if ($.isEmptyObject(this.stocksData[this.orderId])) {
                    this.loadData(row);
                    return;
                }
                var modalHtml = this.getModalHtml();
                var modalClass = this.getModalClass();

                var previewPopup = $('<div/>').html(modalHtml);
                this.modal = previewPopup.modal({
                    title: $.mage.__('Assign Stock'),
                    innerScroll: true,
                    modalClass: modalClass,
                    buttons: [
                        {
                            text: $.mage.__('Cancel'),
                            class: 'action-secondary',
                            attr: {},
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }, {
                            text: $.mage.__('Update'),
                            class: 'update action-primary disabled',
                            attr: {},
                            click: function (event) {
                                self.updateStocksData(row);
                            }
                        }
                    ],
                    closed: function () {
                        self.clearCurrentData();
                        self.reloadPage(row);
                    }
                }).trigger('openModal');

                this.initStockTables();
            },

            reloadPage: function(row) {
                if (row['reloadPage']) {
                    if (typeof row['reloadAfterAssign'] === 'string') {
                        document.location.href = row['reloadAfterAssign'];
                    } else {
                        location.reload();
                    }
                }
            },

            getModalHtml: function () {
                if (this.stocksData[this.orderId].status == false) {
                    return mageTemplate(
                        modalTemplate,
                        {
                            error: this.stocksData[this.orderId].error
                        }
                    );
                }

                var orderItems = this.getOrderItems();
                var stocks = this.getStockItems();
                var stocksCount = _.keys(stocks).length;
                var refundedItems = this.getRefundedItems();
                var hasRefund = this.hasRefund();

                return mageTemplate(
                    modalTemplate,
                    {
                        label: {
                            'qty': $.mage.__('Qty'),
                            'qtyPurchased': $.mage.__('Purchased'),
                            'qtyReturned': $.mage.__('Returned'),
                            'product': $.mage.__('Product'),
                            'update': $.mage.__('Update'),
                            'cancel': $.mage.__('Cancel'),
                            'assigned': $.mage.__('Assigned'),
                            'notAssigned': $.mage.__('Not Assigned'),
                            'inStock': $.mage.__('In Stock'),
                            'outOfStock': $.mage.__('Out of Stock'),
                            'notApplicable': $.mage.__('Not Applicable'),
                            'createStocks': $.mage.__('Please, start by creating sources.')
                        },
                        orderItems: orderItems,
                        orderId: this.orderId,
                        stocks: stocks,
                        stocksCount: stocksCount,
                        error: false,
                        hasRefund: hasRefund,
                        refundedItems: refundedItems
                    }
                );
            },

            hasRefund: function() {
                var refundedItems = this.getRefundedItems();
                return _.reduce(_.values(refundedItems), function(memo, num){ return memo + num; }, 0);
            },

            getRefundedItems: function() {
                var refunds = [];
                $.each(this.getOrderItems(), function (i, orderItem) {
                    var assignedQty = 0;
                    $.each(orderItem.stockItems, function (i, stockItem) {
                        assignedQty += stockItem.assignedQty;
                    });

                    refunds[i] = 0;
                    if (orderItem.qtyOrdered < assignedQty) {
                        refunds[i] = assignedQty - orderItem.qtyOrdered;
                    }
                });

                return refunds;
            },

            getModalClass: function () {
                var modalClass = this.modalClass;

                if (this.hasRefund()) {
                    modalClass += ' has-refund';
                }

                var stocksCount = 0;
                if (this.stocksData[this.orderId].status != false) {
                    var stocks = this.getStockItems();
                    stocksCount = _.keys(stocks).length;
                }

                return modalClass + ' stocks-count-' + stocksCount;
            },

            getFieldHandler: function (row) {
                return this.preview.bind(this, row);
            },

            getOrderItems: function () {
                return this.stocksData[this.orderId]['order_items'];
            },

            getAllOrderItems: function () {
                if ($.isEmptyObject(this.allOrderItems[this.orderId])) {
                    var items = {};
                    $.each(this.getOrderItems(), function (i, item) {
                        items[i] = item;
                        if (item.childItems) {
                            $.each(item.childItems, function (j, child) {
                                items[j] = child;
                            });
                        }
                    });
                    this.allOrderItems[this.orderId] = items;
                }

                return this.allOrderItems[this.orderId];
            },

            getStockItems: function () {
                return this.stocksData[this.orderId]['stocks'];
            },

            initStockTables: function () {
                this.updateLegendBlock();
                this.initNiceScroll();
                this.initInputsActions();
                this.initProductsMarker();
            },

            updateLegendBlock: function () {
                $('.' + this.modalClass + ' .modal-content .legend')
                    .detach().prependTo('.' + this.modalClass + ' .modal-footer');
            },

            initNiceScroll: function () {
                var modalClass = '.' + this.modalClass;
                $(modalClass + ' .modal-content').niceScroll({cursorcolor: "#000", cursorborder: '', cursoropacitymin: 0, cursoropacitymax: 0.5});
                var niceScroll = $(modalClass + ' .assign-stock-tables .right-col').niceScroll({cursorcolor: "#000", cursorborder: '', cursoropacitymin: 0, cursoropacitymax: 0.5});

                $(modalClass).mouseover(function () {
                    niceScroll.resize();
                    $(modalClass + ' .modal-content .nicescroll-rails.nicescroll-rails-hr')
                        .css('top', $(modalClass + ' .modal-header').outerHeight() + $(modalClass + ' .modal-content').outerHeight() - 5);
                    $(modalClass + ' .modal-inner-wrap > .nicescroll-rails.nicescroll-rails-hr').remove();
                });
            },

            clearCurrentData: function () {
                this.stocksData[this.orderId] = {};
                this.allOrderItems[this.orderId] = {};

                $('.iwd-multi-stock-assign-modal').remove();
            },

            initInputsActions: function () {
                var self = this;
                $(this.stockTablesClass).find('input.qty-assigned').on('keypress', function (e) {
                    return self.initInputsKeypress(e, this);
                }).on('focus', function () {
                    self.initInputsFocus(this);
                }).on('click', function () {
                    self.initInputsClick(this)
                }).on('change paste blur', function () {
                    self.initInputsChange(this);
                });
            },

            initInputsKeypress: function (e, item) {
                if (e.which == 13 || e.which == 8) {
                    return 1;
                }
                if ('+-*/'.indexOf(String.fromCharCode(e.which)) != -1) {
                    return $(item).val($(item).val());
                }
                return ('1234567890.+-*/'.indexOf(String.fromCharCode(e.which)) != -1);
            },

            initInputsFocus: function (item) {
                $(item).attr('prev-value', $(item).val());
                $(item).select();
            },

            initInputsClick: function (item) {
                var value = parseFloat($(item).val());

                if (!value || isNaN(value)) {
                    var orderedQty = this.getOrderedItemQty(item);
                    value = orderedQty - this.getStockItemAssignedQty(item);
                    value = this.getValidatedQty(item, value);
                    if (value > 0) {
                        this.changeProductMarker(item);
                        $(item).val(value);
                    }
                }

                $(item).select();
            },

            initInputsChange: function (item) {
                var value = $(item).val();
                value = eval(value);
                value = value && (value > 0) ? value : 0;
                var orderedQty = this.getOrderedItemQty(item);
                var assignedQty = this.getStockItemAssignedQty(item);

                if (assignedQty > orderedQty) {
                    var preValue = parseFloat($(item).attr('prev-value')); preValue = preValue ? preValue : 0;
                    if (value > preValue) {
                        value = preValue;
                    }
                } else {
                    value = this.getValidatedQty(item, value);
                }

                this.updateQty(item, value);
                this.updateStockItemQty(item);
                this.changeProductMarker(item);
                $(item).attr('prev-value', value);
            },

            getItemId: function (item) {
                return $(item).data('item-id');
            },

            getProductId: function (item) {
                return $(item).data('product-id');
            },

            getStockId: function (item) {
                return $(item).data('stock-id');
            },

            getOrderedItemQty: function (item) {
                var itemId = this.getItemId(item);
                return this.getAllOrderItems()[itemId].qtyOrdered;
            },

            getStockItemAssignedQty: function (item) {
                var itemId = this.getItemId(item);
                var qtyApplied = 0;
                $.each($('.stocks-table .item-' + itemId + ' .qty-assigned'), function (i, item) {
                    var value = parseFloat($(item).attr('value'));
                    value = value ? value : 0;
                    qtyApplied += value;
                });
                return qtyApplied;
            },

            getValidatedQty: function (item, value) {
                var itemId = this.getItemId(item);
                var stockId = this.getStockId(item);
                var orderItem = this.getAllOrderItems()[itemId];
                var stockItem = orderItem.stockItems[stockId];
                var qty = orderItem.qtyOrdered - this.getStockItemAssignedQty();

                qty = qty < 0 ? 0 : qty;
                qty = stockItem.isInStock ? Math.min(qty, stockItem.allowedQty + stockItem.assignedQty) : 0;

                return Math.min(qty, value)
            },

            updateQty: function (item, value) {
                $(item).val(value);
                $(item).attr('value', value);

                if (this.getZippedForm() == this.initZippedForm) {
                    $('.iwd-multi-stock-assign-modal button.update').addClass('disabled');
                } else {
                    $('.iwd-multi-stock-assign-modal button.update').removeClass('disabled');
                }
            },

            getZippedForm: function() {
                var data = '';
                $('.qty-assigned').serializeArray().map(function(x){data += x.value + ';';});
                return data;
            },

            updateStockItemQty: function (item) {
                var preValue = parseFloat($(item).attr('prev-value')); preValue = preValue ? preValue : 0;
                var value = parseFloat($(item).val()); value = value ? value : 0;
                var qtyInStock = parseFloat($(item).closest('td').prev('td').find('input').val());

                var qty = qtyInStock + preValue - value;
                var productId = this.getProductId(item);
                var stockId = this.getStockId(item);
                $.each($('input[name="stock['+productId+']['+stockId+']"]'), function(i, stockItem){
                    $(stockItem).val(qty);
                });
            },

            changeProductMarker: function (item) {
                var assigned = this.getOrderedItemQty(item) == this.getStockItemAssignedQty(item);
                var refunded = this.getOrderedItemQty(item) < this.getStockItemAssignedQty(item);
                var itemId = this.getItemId(item);

                if (assigned) {
                    $('.item-' + itemId + ' .product-name i').attr('class', 'fa fa-check');
                    $('.item-' + itemId).addClass('assigned').removeClass('refunded');
                } else if (refunded) {
                    $('.item-' + itemId + ' .product-name i').attr('class', 'fa fa-arrow-down');
                    $('.item-' + itemId).removeClass('assigned').addClass('refunded');
                } else {
                    $('.item-' + itemId + ' .product-name i').attr('class', 'fa fa-times');
                    $('.item-' + itemId).removeClass('assigned').removeClass('refunded');
                }

                var parentId = $(item).closest('tr').data('parent-item');
                if ($('.products-table .parent-item-' + parentId).not('.parent').length == $('.products-table .assigned.parent-item-' + parentId).not('.parent').length) {
                    $('.item-' + parentId).addClass('assigned');
                } else {
                    $('.item-' + parentId).removeClass('assigned');
                }
            },

            initProductsMarker: function () {
                var self = this;
                var items = $('.stocks-table tbody tr > td:nth-child(2) .qty-assigned');
                $.each(items, function (i, item) {
                    self.changeProductMarker(item);
                });
            },

            showModal:function (response) {
                $('.iwd-order-manager-popup').removeClass('_show').addClass('_hide');

                var buttons = [
                    {
                        text: $t('Ok'),
                        class: 'iwd-om-popup-ok',
                        click: function () {
                            this.closeModal();
                        }
                    }
                ];

                if (response.ext_url) {
                    buttons.push({
                        text: $t('Upgrade'),
                        class: 'iwd-om-popup-upgrade',
                        click: function () {
                            window.open(response.ext_url, '_blank');
                            this.closeModal();
                        }
                    });
                }

                modalAlert({
                    title: $t('Order Manager'),
                    content: response.result,
                    modalClass: "iwd-order-manager-popup",
                    clickableOverlay: false,
                    buttons: buttons
                });
            }
        };
    });