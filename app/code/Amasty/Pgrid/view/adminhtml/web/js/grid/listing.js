define([
  'Magento_Ui/js/grid/listing',
  'uiLayout'
], function(Listing, layout){
    return Listing.extend({
        defaults: {
            amastyEditorConfig: {
                name: '${ $.name }_amasty_editor',
                component: 'Amasty_Pgrid/js/grid/editing/editor',
                columnsProvider: '${ $.name }',
                dataProvider: '${ $.provider }',
                enabled: false
            }
        },
        initialize: function () {

            this._super()
                .initAmastyEditor();

            return this;
        },
        initAmastyEditor: function(){
            if (this.amastyEditorConfig.enabled) {
                layout([this.amastyEditorConfig]);
            }
        }
    })
});