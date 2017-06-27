define([
    'jquery',
    'jquery/ui',
    'uiRegistry'
], function ($, jQueryUi, registry) {

    $.widget('mage.amreportsDashboard', {
        options: {
            contentSelector: '[data-role="amreports-content"]',
            componentName: 'amreports_reload=1'
        },

        funnelForm: null,
        salesForm: null,
        storeForm: null,

        _create: function () {
            this.funnelForm = $(this.element).find('#funnel');
            $(this.funnelForm).change($.proxy(this.funnelFormReload, this));

            this.salesForm = $(this.element).find('#salesForm');
            $(this.salesForm).change($.proxy(this.salesFormReload, this));

            this.storeForm = $('[data-role="amreports-toolbar"]').find('#report_toolbar');
            $(this.storeForm).change($.proxy(this.changeStore, this));

            var self = this;
            $(".switch .options .item").on( "click", function() {
                self.changeWidget($(this));
            });
            $("body").on( "click", function() {
                $(this).find('.switch .options').hide();
            });
            $(".switch").on( "click", function(e) {
                $(this).find('.options').toggle();
                e.stopPropagation();
            });
        },

        changeStore: function() {
            var formData = $(this.storeForm).serializeArray();
            var requestData = {};

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];

                requestData[input.name] = input.value;
            }
            $.ajax({
                url: '',
                method: 'GET',
                showLoader: true,
                data: {amreports: requestData},
                success: function (response) {
                    window.location.reload();
                }
            });
        },

        changeWidget: function(elem) {
            var requestData = {};
            requestData['parent'] = elem.data('parent');
            requestData['widget'] = elem.attr('name');
            $.ajax({
                url: '',
                method: 'GET',
                showLoader: true,
                data: {amreports: requestData, 'amaction': 'widget'},
                success: function (response) {
                    elem.parents('.widget').find('#header').html(response.title);
                    elem.parents('.widget').find('.icon').attr('src', response.icon);
                    elem.parents('.widget').find('.total').html(Math.ceil(response.value));
                    elem.parents('.widget').find('.more').attr('href', response.link);
                }
            });
        },

        funnelFormReload: function () {
            var formData = $(this.funnelForm).serializeArray();
            var requestData = {};

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];
                requestData[input.name] = input.value;
            }

            var contentBlock = $(this.options.contentSelector);
            contentBlock.css({opacity: .3});
            $.ajax({
                url: '',
                method: 'GET',
                showLoader: true,
                data: {amreports: requestData, 'amaction': 'funnel'},
                success: function (response) {
                    var data = JSON.parse(response);
                    $('#viewedCount').html(Math.ceil(data.viewedCount));
                    $('#addedCount').html(Math.ceil(data.addedCount));
                    $('#orderedCount').html(Math.ceil(data.orderedCount));

                    $('#notViewed').html(data.notViewed);
                    $('#abandoned').html(data.abandoned);

                    $('#viewedPercent').html(Math.ceil(data.viewedPercent)+'%');
                    $('#addedPercent').html(Math.ceil(data.addedPercent)+'%');
                }
            });
        },

        salesFormReload: function () {
            var formData = $(this.salesForm).serializeArray();
            var requestData = {};

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];
                requestData[input.name] = input.value;
            }

            var contentBlock = $(this.options.contentSelector);
            contentBlock.css({opacity: .3});
            $.ajax({
                url: '',
                method: 'GET',
                showLoader: true,
                data: {amreports: requestData, 'amaction': 'sales'},
                success: function (response) {
                    window.chartData = [];
                    for(var i=0; i<response.items.length;i++) {
                        window.chartData.push({
                            date: response.items[i].period,
                            visits: response.items[i].base_grand_total
                        });
                    };
                    window.currentChart.dataProvider = window.chartData;
                    window.currentChart.validateData();
                }
            });
        },

        widgetFormReload: function () {
            var formData = $(this.salesForm).serializeArray();
            var requestData = {};

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];
                requestData[input.name] = input.value;
            }

            var contentBlock = $(this.options.contentSelector);
            contentBlock.css({opacity: .3});
            $.ajax({
                url: '',
                method: 'GET',
                showLoader: true,
                data: {amreports: requestData, 'amaction': 'sales'},
                success: function (response) {

                }
            });
        }
    });

    return $.mage.amreportsDashboard;
});
