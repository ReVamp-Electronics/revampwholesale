require([
	'jquery',
	'mage/translate'
], function($) {
	$(function(){
		$("#transaction_box_hander").click(function() {
			$("#transaction_history_box").slideToggle();
			if (this.innerHTML == $.mage.__('Hide')) {
				this.innerHTML = $.mage.__('Show');
			} else {
				this.innerHTML = $.mage.__('Hide');
			}
		});
	});
});
