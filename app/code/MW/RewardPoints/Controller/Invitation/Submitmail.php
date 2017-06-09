<?php

namespace MW\RewardPoints\Controller\Invitation;

class Submitmail extends \MW\RewardPoints\Controller\Invitation
{
	public function execute()
	{
		$result = "'".implode(",", $this->getRequest()->getPost('mw_contact_mail'))."'";
    	$contents = "<script type='text/javascript'>
			//<![CDATA[
				var value_old = window.opener.document.getElementById('email').value;
				if (value_old != '') {
					value_old = value_old + ',';
				}
				window.opener.document.getElementById('email').value = value_old + $result;
    			window.close();
    		//]]>
		</script>";

    	echo $contents;
	}
}
