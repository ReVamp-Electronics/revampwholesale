
define([
    'ko',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'uiRegistry',
    'uiClass'
], function (ko, $, _, registry, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            rootSelector: '${ $.columnsProvider }:.admin__data-grid-wrap',
            tableSelector: '${ $.rootSelector } -> table',
            cellSelector: '${ $.tableSelector } tbody tr.data-row div.data-grid-cell-content',
            rowTmpl:
                    '<!-- ko with: _editor -->' +
                        '<!-- ko if: isEditable($row().entity_id, $col.index) -->' +
                            '<!-- ko with: getField($row().entity_id, $col.index) -->' +
                                '<!-- ko template: $parent.fieldTmpl --><!-- /ko -->' +
                            '<!-- /ko -->' +
                        //    '<!-- ko if: isSingleEditing -->' +
                        //        '<!-- ko template: rowButtonsTmpl --><!-- /ko -->' +
                        //    '<!-- /ko -->' +
                        '<!-- /ko -->' +
                   '<!-- /ko -->',
            headerButtonsTmpl:
                '<!-- ko template: headerButtonsTmpl --><!-- /ko -->'
        },
        initialize: function () {
            _.bindAll(
                this,
                'initRoot',
                'initCell',
                'cellBindings'
            );

            this._super();

            this.model = registry.get(this.model);

            $.async(this.rootSelector, this.initRoot);
            $.async(this.cellSelector, this.initCell);

            return this;
        },

        initRoot: function (node) {
            $(this.headerButtonsTmpl)
                .insertBefore(node)
                .applyBindings(this.model);


            this.initScroll();
            
            return this;
        },
        initScroll: function(){
            var win = $(window);
           var offsetTop;

           win.on('scroll', function () {
               var panel = $('#ampgrid-data-grid-info-panel');
               if (!offsetTop)
                   offsetTop= panel.offset().top - panel.height();
               var isActive = (win.scrollTop() > offsetTop);
               if (isActive) {
                   panel.addClass('_amasty_pgrid_fixed');
               } else {
                   panel.removeClass('_amasty_pgrid_fixed');
               }
           });
        },
        cellBindings: function (ctx) {
            var model = this.model;

            return {
                visible: ko.computed(function () {
                    var visible = false;

                    if (model.rowsData[ctx.$index]) {
                        var productId = model.rowsData[ctx.$index].entity_id,
                            colIndex = ctx.$col.index,
                            field = model.getField(productId, colIndex);

                        visible = !field || field.visible() === false;
                    }

                    return visible;
                })
            };
        },
        initCells: function(){
            var cell = this;
            $.async(this.cellSelector, function(node){
                cell.initCell(node);
            });
        },
        initColumn: function(colIndex){
            var cell = this;
            $.async(this.cellSelector, function(node){

                if (ko.contextFor(node).$col.index == colIndex){
                    cell.initCell(node);
                }
            });
        },
        initCell: function (node) {
            var koNode = ko.contextFor(node);
            if (this.model.isEditableColumn(koNode.$col.index) &&
                koNode.hasAmpgridEditor !== true){

                var $editingCell;

                $(node).extendCtx({
                    _editor: this.model
                }).bindings(this.cellBindings);

                $editingCell= $(this.rowTmpl)
                    .insertBefore(node)
                    .applyBindings(node);

                ko.utils.domNodeDisposal.addDisposeCallback(node, this.removeEditingCell.bind(this, $editingCell));

                koNode.hasAmpgridEditor = true;
            }

            return this;
        },
        removeEditingCell: function (cell) {
            _.toArray(cell).forEach(ko.removeNode);
        }
    });
});
