define([
  'ko',
  'jquery',
  'mageUtils',
  'Magento_Ui/js/grid/controls/columns',
  'uiLayout',
], function(ko, $, utils, Columns, layout){


  var r = Columns.extend({
    defaults: {
      selectedTab: 'tab1',
      template: 'Amasty_Pgrid/ui/grid/controls/columns',
      clientConfig: {
          component: 'Magento_Ui/js/grid/editing/client',
          name: '${ $.name }_client'
      },
      modules: {
          client: '${ $.clientConfig.name }',
          source: '${ $.provider }',
          editorCell: '${ $.editorCellConfig.provider }',
          listingFilter: '${ $.listingFilterConfig.provider }'

      }
      //storageConfig: {
      //    provider: '${ $.storageConfig.name }',
      //    name: '${ $.name }_storage',
      //    component: 'Magento_Ui/js/grid/controls/bookmarks/storage1234',
      //}
    },
    initElement: function (el){
      el.track(['label', 'ampgrid_editable', 'ampgrid_filterable'])
      el.headerTmpl = "Amasty_Pgrid/ui/grid/columns/text";
    },
    hasSelected: function(tabKey){
      return this.selectedTab == tabKey;
    },
    getDefaultColumns: function(){
      var c = [];
      this.elems.each(function(el){
        if (el.ampgrid && !el.amastyExtra && !el.amastyAttribute){
          c.push(el);
        }
      });
      return c;
    },
    getAtttributeColumns: function () {
      var c = [];
      this.elems.each(function(el){
        if (el.ampgrid && el.amastyAttribute){
          c.push(el);
        }
      });
      return c;
    },
    getExtraColumns: function () {
      var c = [];
      this.elems.each(function(el){
        if (el.ampgrid && el.amastyExtra){
          c.push(el);
        }
      });
      return c;
    },
    close: function () {
        return this;
    },
    save: function () {
      var columns = this;
      columns.editorCell().model.columns('showLoader');
      this.elems.each(function(el){
        if (el.ampgrid){
            var current = columns.storage().get("current.columns." + el.index);
            if (current){
              current.visible = el.ampgrid.visible;
              current.ampgrid_title = el.ampgrid.title;
              current.ampgrid_editable = el.ampgrid.editable;
              current.ampgrid_filterable = el.ampgrid.filterable;
            }
        }
      });

      var data = this.source().get('params');
      data.data = JSON.stringify({'current': this.storage().current});

      this.client()
          .save(data)
          .done(this.onDataSaved)
          .fail(this.onSaveError);


      return this;
    },
    onDataSaved: function(data){
      var columns = this;

      this.source().onReload(data['grid']);

      var data = this.source().get('params');
      data.data = null;

      this.elems.each(function(el) {
        if (el.ampgrid) {
          el.visible = el.ampgrid.visible;
          el.label = el.ampgrid.title;
          el.ampgrid_editable = el.ampgrid.editable;
          el.ampgrid_filterable = el.ampgrid.filterable;
          columns.editorCell().initColumn(el.index);

          var filter = columns.listingFilter().elems.findWhere({
              index: el.index
          });

          if (!filter && el.ampgrid.filterable){
            el.filter = el.default_filter;
            columns.listingFilter().addFilter(el);
          }

          if (filter && !el.ampgrid.filterable){
            filter.visible(false);
          } else if (filter && el.visible && el.ampgrid.filterable){
            filter.visible(true);
          }
        }
      })
    },
    onSaveError: function(){

    },
    initialize: function () {

      _.bindAll(this, 'onDataSaved', 'onSaveError');

      this._super();

      layout([this.clientConfig]);

      return this;

    },
    initObservable: function () {
      this._super()
          .track(['selectedTab']);

      return this;
    },
  });



  return r;
});