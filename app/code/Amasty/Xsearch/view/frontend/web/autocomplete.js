define([
    'Magento_Search/form-mini',
    'jquery',
    'underscore',
    'mage/template',
    'jquery/ui'
], function (quickSearch, $, _, mageTemplate) {
    'use strict';

    function isEmpty(value) {
        return (value.length === 0) || (value == null) || /^\s+$/.test(value);
    }

    var autocomplete = {
        ajaxRequest: null,
        url: null,
        timer: null,
        delay: 500,
        currentUrlEncoded: null,
        init: function(url, layout, currentUrlEncoded){
            this.url = url;
            this.layout = layout;
            this.currentUrlEncoded = currentUrlEncoded;
            this.extend();
        },
        extend: function(){
            var _caller = this;
            var methods = {
                options: {
                    amastyAutocomplete: this,
                    minChars: this.layout.minChars
                },
                _onPropertyChange: function(){
                    if (_caller.timer != null){
                        clearTimeout(_caller.timer);
                    }
                    _caller.timer = setTimeout(function(){
                        _caller._onPropertyChange.call(this);
                    }.bind(this), _caller.delay)
                },
                _create: this._create,
                _amastyXsearchOnClick: this.onClick,
                _amastyXsearchShowLoader: this.showLoader,
                _amastyXsearchHideLoader: this.hideLoader,
                _amastyXsearchShowPopup: this.showPopup
            };

            $.extend(true, quickSearch.prototype, methods);
        },
        showLoader: function(){
            var $loader = $('#amasty_xsearch_loader');
            $loader.removeClass('amasty-xsearch-hide');


            var margin = $(this.searchForm).find('.control').height() / 2 + $loader.height() / 2;
            $loader.css({'margin-top': '-' + margin + 'px'});
            $(this.submitBtn).addClass('amasty-xsearch-hide');

        },
        hideLoader: function (){
            var $loader = $('#amasty_xsearch_loader');
            $loader.addClass('amasty-xsearch-hide');
            $(this.submitBtn).removeClass('amasty-xsearch-hide');
        },
        onClick: function(){
            var $preload = $('#amasty_xsearch_preload');
            if ($preload && $preload.length > 0){
                this._amastyXsearchShowPopup($preload.html());
            }
        },
        showPopup: function(html){
            var amastyAutocomplete = this.options.amastyAutocomplete;
            var searchField = this.element,
                clonePosition = {
                    position: 'absolute',
                    // Removed to fix display issues
                    // left: searchField.offset().left,
                    // top: searchField.offset().top + searchField.outerHeight(),
                    width: amastyAutocomplete.layout.width ?
                            amastyAutocomplete.layout.width:
                            searchField.outerWidth()
                },
                source = this.options.template,
                template = mageTemplate(source),
                dropdown = $('<ul role="listbox"></ul>'),
                value = this.element.val();

            dropdown.append(html);

            this.responseList.indexList = this.autoComplete.html(dropdown)
                .css(clonePosition)
                .show()
                .find(this.options.responseFieldElements + ':visible');

            if (this.responseList.indexList.length > 0){
                this.autoComplete.show();
            } else {
                this.autoComplete.hide();
            }

            this._resetResponseList(false);
            this.element.removeAttr('aria-activedescendant');

            if (this.responseList.indexList.length) {
                this._updateAriaHasPopup(true);
            } else {
                this._updateAriaHasPopup(false);
            }

            this.responseList.indexList
                .on('click', function (e) {
                    var $target = $(e.target);

                    if (!$target.attr('click-url')){
                        $target = $(e.target).closest('[click-url]');
                    }
                    if ($(e.target).closest('[item-actions=1]').length === 0) {
                        document.location.href = $target.attr('click-url');
                    } else {
                        this.element.focus();
                        this.element.trigger('focus');
                    }
                }.bind(this))
                .on('mouseenter mouseleave', function (e) {
                    this.responseList.indexList.removeClass(this.options.selectClass);
                    $(e.target).addClass(this.options.selectClass);
                    this.responseList.selected = $(e.target);
                    this.element.attr('aria-activedescendant', $(e.target).attr('id'));
                }.bind(this))
                .on('mouseout', function (e) {
                    if (!this._getLastElement() && this._getLastElement().hasClass(this.options.selectClass)) {
                        $(e.target).removeClass(this.options.selectClass);
                        this._resetResponseList(false);
                    }
                }.bind(this));
        },
        _onPropertyChange: function(){
            var amastyAutocomplete = this.options.amastyAutocomplete;
            var searchField = this.element,
                clonePosition = {
                    position: 'absolute',
                    // Removed to fix display issues
                    // left: searchField.offset().left,
                    // top: searchField.offset().top + searchField.outerHeight(),
                    width: amastyAutocomplete.layout.width ?
                            amastyAutocomplete.layout.width:
                            searchField.outerWidth()
                },
                source = this.options.template,
                template = mageTemplate(source),
                dropdown = $('<ul role="listbox"></ul>'),
                value = this.element.val();

            this.submitBtn.disabled = isEmpty(value);

            var minChars = this.options.minChars ? this.options.minChars : this.options.minSearchLength;

            if (value.length >= parseInt(minChars, 10)) {
                this._amastyXsearchShowLoader();

                if (amastyAutocomplete.ajaxRequest){
                    amastyAutocomplete.ajaxRequest.abort();
                }

                amastyAutocomplete.ajaxRequest = $.get(amastyAutocomplete.url, {q: value, uenc: amastyAutocomplete.currentUrlEncoded}, $.proxy(function (data) {
                    this._amastyXsearchShowPopup(data.html);

                    this._amastyXsearchHideLoader();
                }, this));

            } else {
                this._resetResponseList(true);
                this.autoComplete.hide();
                this._updateAriaHasPopup(false);
                this.element.removeAttr('aria-activedescendant');
            }
        },
        _create: function () {
            this.responseList = {
                indexList: null,
                selected: null
            };
            this.autoComplete = $(this.options.destinationSelector);
            this.searchForm = $(this.options.formSelector);
            this.submitBtn = this.searchForm.find(this.options.submitBtn)[0];
            this.searchLabel = $(this.options.searchLabel);

            _.bindAll(this, '_onKeyDown', '_onPropertyChange', '_onSubmit', '_amastyXsearchOnClick');

            this.submitBtn.disabled = true;

            this.element.attr('autocomplete', this.options.autocomplete);

            var timer;

            this.element.on('blur', $.proxy(function () {

                timer = setTimeout($.proxy(function () {
                    if (this.autoComplete.is(':hidden')) {
                        this.searchLabel.removeClass('active');
                    }
                    this.autoComplete.hide();
                    this._updateAriaHasPopup(false);
                }, this), 250);
            }, this));

            this.element.trigger('blur');

            this.element.on('focus', $.proxy(function () {
                if (timer != null){
                    clearTimeout(timer);
                }

                this.searchLabel.addClass('active');
            }, this));
            this.element.on('keydown', this._onKeyDown);
            this.element.on('input propertychange', this._onPropertyChange);
            this.element.on('input click', this._amastyXsearchOnClick);

            this.searchForm.on('submit', $.proxy(function() {
                this._onSubmit();
                this._updateAriaHasPopup(false);
            }, this));
        }
    }
    return autocomplete;
})
