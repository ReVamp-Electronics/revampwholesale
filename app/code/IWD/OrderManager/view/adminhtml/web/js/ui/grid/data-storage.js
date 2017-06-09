define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var mixin = {
        /**
         * @param {Object} data - Data associated with request.
         * @param {Object} params - Request parameters.
         * @returns {DataStorage} Chainable.
         */
        cacheRequest: function (data, params) {
            var cached = this.getRequest(params);

            if (cached) {
                this.removeRequest(cached);
            }

            var additionalParams = $.extend({}, data);
            additionalParams.totalRecords = null;
            additionalParams.items = null;
            delete additionalParams.totalRecords;
            delete additionalParams.items;

            this._requests.push({
                ids: this.getIds(data.items),
                params: params,
                totalRecords: data.totalRecords,
                additionalParams: additionalParams
            });

            return this;
        },

        /**
         * Forms data object associated with provided request.
         *
         * @param {Object} request - Request object.
         * @returns {jQueryPromise}
         */
        getRequestData: function (request) {
            var defer = $.Deferred(),
                resolve = defer.resolve.bind(defer),
                delay = this.cachedRequestDelay,
                result;

            result = $.extend({
                items: this.getByIds(request.ids),
                totalRecords: request.totalRecords
            }, request.additionalParams);

            delay ?
                _.delay(resolve, delay, result) :
                resolve(result);

            return defer.promise();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
