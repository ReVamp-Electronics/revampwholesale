/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define([
    'jquery'
], function($) {
    $.widget('awrma.awRmaButton', {
        options: {
            newLocation: ''
        },
        _create: function() {
            this._bind();
        },
        _bind: function() {
            $(this.element).on('click', $.proxy(this.onButtonClick, this));
        },
        onButtonClick: function() {
            window.location = this.options.newLocation;
        }
    });

    return $.awrma.awRmaButton;
});
