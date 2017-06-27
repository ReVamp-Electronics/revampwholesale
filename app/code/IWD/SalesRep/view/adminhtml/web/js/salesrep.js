define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($, modal) {
    $.widget('iwd.salesrep', {
        _create: function () {
            var self = this;
            self.assignCustomerHandler();
            self.commissionTypeChangeHandler();
            self.submitSaveCommissionHandler();   
            self.updateCommissionHandler();
        },
        updateCommissionHandler: function() {
            var self = this;
            $(document).on('click', '.iwdsr-update-commission', function(e) {
                e.stopPropagation();
                e.preventDefault();
                var _this = $(this);
                if (_this.closest('tr').hasClass('iwdsr-disabled')
                     || _this.hasClass('disabled')
                )
                    return;

                $.ajax({
                    showLoader: true, 
                    url: self.options.commissionBlockUrl,
                    data: { salesrep_id: self.options.salesrepId, customer_id: _this.data('customerId') },
                    dataType: 'json',
                    success: function(response) {
                         $('<div />').html(response.html)
                           .modal({
                               modalClass: 'iwdsr-commission-modal',
                               title: 'Commission Settings',
                               autoOpen: true,
                               opened: function(e) {
                                   $(e.target).find('#commission_type').trigger('change');
                               },
                               buttons: [{
                                    text: 'Save',
                                    class: 'action primary' + (!response.res ? ' disabled' : ''),
                                    click: function(e) {
                                        var _form = $(e.target).closest('.modal-popup').find('form');
                                        if (_form.length)
                                            self.submitSaveCommission(_form);
                                    }
                                }]
                            });
                    }
                });
               
            });   
        },
        submitSaveCommission: function($form) {
            var self = this;
            var _modal = $form.closest('.modal-popup'), _modalContent = _modal.find('.modal-content');
            $.ajax({
                url: $form.attr('action'),
                showLoader: true, 
                data: $form.serialize(),
                success: function(response) {
                    if (response.res == true) {
                        customerId = $form.find('[name=customer_id]').val();
                        $('#id_' + customerId).closest('tr').find('td.col-salesrep_action').html(response.html); // update grid value
                        _modal.find('[data-role=closeBtn]').click(); // close popup
                    } else {
                        self.showMessage(response.message, _modalContent);
                    }
                },
                error: function(response) {
                    self.showMessage(response.statusText, _modalContent);
                }
            });        
        },
        submitSaveCommissionHandler: function() {
            var self = this;
            $(document).on('submit', '#salesrep_commission_form', function(e){
                e.preventDefault();
                self.submitSaveCommission($(this));
            });
        },
        showMessage: function(text, modalContent) {
            var msg = $('<p>')
                .css({color: 'red'})
                .attr('class', 'iwdsr-delayed-exit')
                .text(text);
                modalContent.append(msg);
            setTimeout(function(){
                msg.hide(function(e){
                    $(this).remove();
                });
            }, 2000);
        },
        assignCustomerHandler: function() {
            var self = this;
            // disable click on checkbox loader
            $(document).on('click', '.iwd-sr-loader', function(e){
                e.preventDefault();
                e.stopPropagation();
                return;
            });  
            $(document).on('change', '#iwd_salesrep_customers .iwd-salesrep-assign [type=checkbox]', function(e){
                e.preventDefault();
                e.stopPropagation();
                var _checkBox = $(this);
                if (_checkBox.parent().hasClass('iwd-sr-loader'))
                    return;
                var customerId = _checkBox.val();
                var _checked = +(_checkBox.is(':checked'));
                _checkBox.wrap('<span class="fa fa-circle-o-notch fa-spin iwd-sr-loader"></span>');

                // disable Update btn for commission
                if (!_checked) {
                    _checkBox.closest('tr').find('.iwdsr-update-commission').addClass('disabled');
                }
                $.ajax({
                    url: self.options.attachUrl,
                    method: 'post',
                    data: { 
                        customer_id: customerId, 
                        salesrep_id: self.options.salesrepId,
                        attach: _checked,
                        },
                    dataType: 'json',
                    success: function(response) {
                        delete window.iwdsrxhr;
                        _checkBox.unwrap();
                        if (response.res == true) {
                            var actionCell = _checkBox.closest('tr').find('.col-salesrep_action');
                            actionCell.html(response.actionHtml);
                        } else {
                            _checkBox.prop("checked", _checkBox.prop('checked') ? false : true);
                            alert(response.message);
                        }
                    }
                });
            });
        },
        commissionTypeChangeHandler: function() {
            $(document).on('change', '[name=commission_type]', function(e){
                var _this = $(this);
                var applyWhenSelect = _this.closest('form').find('#commission_apply');
                applyWhenSelect.prop('disabled', _this.val() == 'fixed');
            });
        }
    });

    return $.iwd.salesrep;
});