/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'jquery'
    ],
    function ($) {
        'use strict';

        var data =  {
            init: function(options) {
                for (var name in options) {
                    this[name] = options[name];
                }
            },
            get: function(name, def) {
                if (typeof this[name] !== "undefined") {
                    return this[name];
                }
                return def;
            },
            "Aheadworks_Rma/js/model/customer/request/view/address": function(options) {
                data.init(options);
            }
        };
        return data;
    }
);
