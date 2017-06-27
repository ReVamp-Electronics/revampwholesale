<?php

namespace MW\RewardPoints\Controller\Invitation;

class Widget extends \MW\RewardPoints\Controller\Invitation
{
	public function execute()
	{
		$body = '<html><head><script type="text/javascript" src="https://www.plaxo.com/ab_chooser/abc_comm.jsdyn"></script></head><body></body></html>';
    	$this->getResponse()->setBody($body);

    	return;
	}
}
