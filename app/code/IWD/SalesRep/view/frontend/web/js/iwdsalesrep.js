define([
    'jquery',
    'b2bnicescroll',
    'b2bmodal'
], function($, $b2b) {
    $.widget('iwd.salesrep', {
        options: {
            searchBySelector: '.iwdsr-name, .iwdsr-email'
        },
        _create: function () {
            var self = this;
            // detect if loaded footer
            if(!$('.iwdsr-footer').length){
                $('.b2b-sticky-footer').addClass('no_iwdsr_footer');
            }
            else {
              $('.b2b-sticky-footer').css('height','100px');
            }

            this.retrieveCustomerListHandler();
            this.chooseCustomerHandler();
            this.filterByFirstLetter();
            this.filterHandler();
            $(window).resize(function(){
                self.imitateNthChild();
                self.reinitCustomerListScroll();
            });

            // if($('.b2b-sticky-footer').children().hasClass('iwd-b2b-footer-children')){
            //     $('.b2b-sticky-footer').css('height','100px');
            // }
        },
        detectAvailableFilterLetters: function() {
            var self = this;
            var even_one = false;
            $('.iwdsr.letter-search li[data-letter]').each(function(e){
                firstLetter = $(this).data('letter');
                var has = self.checkLetterExists(firstLetter);
                if(!has)
                    $(this).hide();
                else
                    even_one = true;
            });
            if(!even_one){
                $('.iwdsr-filter-reset').hide();
            }
        },
        checkLetterExists: function(firstLetter) {
            var self = this;
            var items = $('.iwdsr.customer-item');
            var foundLetter = !Boolean(firstLetter);
            $.each(items, function(k, item) {
                if(foundLetter)
                    return;

                item = $(item);
                var searchBy = item.find(self.options.searchBySelector);
                $.each(searchBy, function(k, searchItem) {
                    searchItem = $(searchItem);
                    var text = searchItem.text();
                    var words = text.split(/\s+/);

                    if (!foundLetter) {
                         $.each(words, function(k, word){
                            if (word.toLowerCase()[0] == firstLetter.toLowerCase()) {
                                foundLetter = true;
                            }
                        });
                    }
                });
            });
            return foundLetter;
        },
        retrieveCustomerListHandler: function() {
            var self = this;

            $(document).on('click', '.iwdsr-choose-customer-btn', function(e){
                self._setCustomerBtnLoading();
                e.preventDefault();
                $.ajax({
                    url: self.options.jsConfig.customersListUrl,
                    data: {},
                    dataType: 'json',
                    success: function(response) {
                        self._unsetCustomerBtnLoading();
                        var modal = $('#iwdsr-customers-list-modal');
                        modal.find('.iwdsr-customers-list-wrap').html(response.html);

                        modal.on('shown.bs.modal', function (e) {
                            self.initOpenedSRModal();
                        });

                        var options = {"backdrop":true, "show":true};
                        modal.modaliwd(options);

                        self.detectAvailableFilterLetters();
                        self.initCustomerListScroll(500);
                    }
                });
            });
        },
        filterHandler: function() {
            var self = this;
            $(document).on('keyup', '[name=salesrep_customer_search]', function(e){
                var _this = $(this),
                    searchString = _this.val();
                    firstLetter = self.getActiveFirstLetter();
                self.applyFilters(searchString, firstLetter);
            });

            $(document).on('click', '.quick-customer-search', function(e){
                var firstLetter = self.getActiveFirstLetter(),
                    searchString = $('[name=salesrep_customer_search]').val();

                self.applyFilters(searchString, firstLetter);
            });
        },
        getActiveFirstLetter: function() {
            var firstLetterEl = $('.iwdsr.letter-search .active');
            return firstLetterEl.length ? firstLetterEl.data('letter') : false;
        },
        applyFilters: function(searchString, firstLetter) {
            var self = this;
            var items = $('.iwdsr.customer-item');
            self.filterReset();
            var has_results = false;
            $.each(items, function(k, item) {
                var foundLetter = !Boolean(firstLetter), foundWord = searchString == "";
                item = $(item);
                var searchBy = item.find(self.options.searchBySelector);
                $.each(searchBy, function(k, searchItem) {
                    searchItem = $(searchItem);
                    var text = searchItem.text();
                    var words = text.split(/\s+/);

                    if (!foundWord)
                        foundWord = text.search(new RegExp(searchString, 'i')) !== -1;

                    if (!foundLetter) {
                         $.each(words, function(k, word){
                            if (word.toLowerCase()[0] == firstLetter.toLowerCase()) {
                                foundLetter = true;
                            }
                        });
                    }
                });

                if (foundLetter && foundWord) {
                    has_results = true;
                } else {
                    item.addClass('hidden');
                }
            });
            if(!has_results){
                search_str = '';
                if(searchString != '')
                    search_str = search_str+searchString;
                if(firstLetter != ''){
                    if(search_str != '')
                        search_str+=' & ';
                    search_str+=firstLetter;
                }
                $('.iwdsr_list_no_results span').html(search_str);
                $('.iwdsr_list_no_results').show();
                this.destroyCustomerListScroll();
            }
            else{
                $('.iwdsr_list_no_results').hide();
                self.reinitCustomerListScroll();
            }
        },
        filterByFirstLetter: function() {
            var self = this;
            $(document).on('click touchstart', '.iwdsr.letter-search li[data-letter]', function(e){
                var _this = $(this), ul = _this.parent(), firstLetter = _this.data('letter');
                ul.find('li').removeClass('active');
                _this.addClass('active');

                // calculate position of active circle
                var left = (30-_this[0].offsetWidth ) / 2;
                if (_this.is(':first-child'))
                    left += 3;
               $('#iwdsr-inline-styles').text('.iwdsr.letter-search li.active:after { left: -'+left+'px; }');

                self.applyFilters($('[name=salesrep_customer_search]').val(), firstLetter);
            });

            $(document).on('click', '.iwdsr-filter-reset', function(e){
                $(this).parent().find('li').removeClass('active');
                $('[name=salesrep_customer_search]').val('');
                self.applyFilters($('[name=salesrep_customer_search]').val(), false);
            });
        },
        filterReset: function() {
            $('.iwdsr.customer-item').removeClass('hidden');
            this.reinitCustomerListScroll();
        },
        reinitCustomerListScroll: function() {
            this.destroyCustomerListScroll();
            if($('.iwdsr_list_no_results').is(":visible"))
                return;
            this.initCustomerListScroll(100);
        },
        initCustomerListScroll: function(delay) {
            this.imitateNthChild();
            setTimeout(function(){
                if($('.iwdsr_list_no_results').is(":visible"))
                    return;
                $(".iwdsr.customers-list").niceScroll({
                    //autohidemode: false,
                    cursorborder:"",
                    cursorcolor:"#ccc",
                    cursoropacitymax:0.35,
                    cursorwidth:9,
                    boxzoom:false,
                    railoffset:{left:-2}
                });

            }, delay);
        },
        destroyCustomerListScroll: function() {
            try {
                $(".iwdsr.customers-list").niceScroll().remove();
            } catch (exc) { }
        },
        imitateNthChild: function() {
            $('.iwdsr.customer-item').removeClass('nth-3 nth-2 nth-1');
            var windowWidth = $(window).width(), cntInLine = null;
            var visibleItems = $('.iwdsr.customer-item:not(.hidden)');
            if (windowWidth > 543) {
                cntInLine = 3;
            } else if (windowWidth <= 543 && windowWidth > 420) {
                cntInLine = 2;
            } else if (windowWidth <= 420)
                cntInLine = 1;
            if (cntInLine > 1) {
                for (i=0;i<visibleItems.length; i++) {
                    if ((i+1) % cntInLine == 0) {
                        visibleItems.eq(i).addClass('nth-' + cntInLine);
                    }
                }
            }
        },
        chooseCustomerHandler: function() {
            var self = this;
            $(document).on('click', '.iwdsr-customer-item-inner', function(e){
                var form = $(this).find('form');
                $('[name=form_key]').clone().appendTo(form);
                form.submit();
            });
        },
        _setCustomerBtnLoading: function() {
            $('.iwdsr-choose-customer-btn .fa-plus-circle')
            .removeClass('fa-plus-circle')
            .addClass('fa-circle-o-notch fa-spin');
        },
        _unsetCustomerBtnLoading: function() {
            $('.iwdsr-choose-customer-btn .fa-circle-o-notch')
            .removeClass('fa-circle-o-notch fa-spin')
            .addClass('fa-plus-circle');
        },
        initOpenedSRModal: function(modal_identifier){
            var self = this;
            modal_identifier = '#iwdsr-customers-list-modal .b2b-modal-dialog';
            self.valignSRModal(modal_identifier);

            $(window).on('resize', function() {
                self.valignSRModal(modal_identifier);
            });
        },
        valignSRModal: function(modal_identifier){
            var $dialog  = $(modal_identifier);
            if(!$dialog.is(':visible'))
                return;

            offset       = ($(window).height() - $dialog.height()) / 2;
            bottomMargin = parseInt($dialog.css('marginBottom'), 10);
            // Make sure you don't hide the top part of the modal w/ a negative margin if it's longer than the screen height, and keep the margin equal to the bottom margin of the modal
            if(offset < bottomMargin)
                offset = bottomMargin;

            $dialog.css("margin-top", offset);
        }
    });
    return $.iwd.salesrep;
});
