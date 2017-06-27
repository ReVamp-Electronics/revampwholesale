define([
    'jquery',
    'jquery/ui',
    'uiRegistry'
], function ($, jQueryUi, registry) {

    $.widget('mage.amreportsToolbar', {
        options: {
            contentSelector: '[data-role="amreports-content"]',
            componentName: 'amreports_reload=1'
        },

        form: null,

        _create: function () {
            this.form = $(this.element).find('form');

            $(this.form).change($.proxy(this.reload, this));
        },
        
        reload: function () {
            var formData = $(this.form).serializeArray();
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
                data: {amreports: requestData},
                success: function (response) {
                    if ('AmCharts' in window) {
                        AmCharts.clear();
                    }
                    contentBlock.html(response);
                    contentBlock.find("script").each(function(i) {
                        eval($(this).text());
                    });
                    contentBlock.css({opacity: 1});

                    if ('AmCharts' in window) {
                        AmCharts.handleLoad();
                    }
                }
            });

            var dataSource = registry.get(this.options.componentName);

            if (dataSource) {
                dataSource.params.amreports = requestData;
                dataSource.reload();
            }
        }
    });

    return $.mage.amreportsToolbar;
});
