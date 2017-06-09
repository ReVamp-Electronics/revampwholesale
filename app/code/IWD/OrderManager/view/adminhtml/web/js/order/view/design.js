define([
        'jquery',
        'jquery/ui'
    ],

    function($){
        'use strict';

        $.widget('mage.iwdOrderManagerDesign', {
            tabs: $("#sales_order_view_tabs"),
            container: $("#container"),
            bottomPoint: 600,

            init:function(){
                var self = this;
                self.bottomPoint = self.tabs.offset().top + self.tabs.height();

                $(window).scroll(function() {
                    self.hideLeftMenu();
                });

                $(window).resize(function() {
                    self.hideLeftMenu();
                });
            },

            hideLeftMenu:function()
            {
                if(window.innerWidth < 1500){
                    if(this.bottomPoint <= $(window).scrollTop()) {
                        this.tabs.hide();
                        this.container.css('width', '100%').css('width', '-=30px');
                    } else {
                        this.tabs.show();
                        this.container.removeAttr('style');
                    }
                } else {
                    this.tabs.show();
                    this.container.removeAttr('style');
                }
            }
        });

    return $.mage.iwdOrderManagerDesign;
});