/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

/**
 * Initialization widget to move department selector on the contact form
 */
define([
    'jquery'
], function($) {
    "use strict";

    $.widget('mage.awHelpdeskContactForm', {
        options: {
            awHelpdeskSelector: ''
        },

        /**
         * Initialize widget
         */
        _create: function () {
            var departmentEl = $(this.options.awHelpdeskSelector);

            if (departmentEl && departmentEl.length > 0) {
                this.element.after(departmentEl);
                departmentEl.show();
            }
        }
    });

    return $.mage.awHelpdeskContactForm;
});
