require([
    'jquery',
	'jquery/ui',
	'jquery/validate',
	'mage/translate'
], function($) {
	$.validator.addMethod(
		'mw-rewardpoint-validate-coupon-code', function(value) {
			if (value == '') {
				return true;
			} else {
				result = /^[a-zA-Z]+[a-zA-Z0-9]*$/.test(value);
	        	return result;
			}
		},
		$.mage.__('The store code may contain only letters (a-z)(A-Z), numbers (0-9), the first character must be a letter')
	);
});
