<?php

namespace MW\RewardPoints\Controller\Invitation;

// Load openinviter library
if (!class_exists('openinviter')) {
    require_once __DIR__ . '/../../lib/openinviter.php';
}

class Processmail extends \MW\RewardPoints\Controller\Invitation
{
	public function execute()
	{
		$ers = [];
    	$inviter = new \openinviter;
		$oi_services = $inviter->getPlugins();

		$emailBox = $this->getRequest()->getPost('email_box');
		$passwordBox = $this->getRequest()->getPost('password_box');
		$providerBox = $this->getRequest()->getPost('provider_box');

		$inviter ->startPlugin($providerBox);
		$internal = $inviter->getInternalError();
		if ($internal) {
			$ers['inviter'] = $internal;
		} elseif (!$inviter->login($emailBox, $passwordBox)) {
			$internal = $inviter->getInternalError();
			if ($internal) {
				$ers['login'] = $internal;
			} else {
				$ers['login'] = __("Login failed. Please check the email and password you have provided and try again later !");
			}
		} elseif (false === $contacts = $inviter->getMyContacts()) {
			$ers['contacts'] = __("Unable to get contacts !");
		} else {
			$resultPage = $this->_resultPageFactory->create();
			return $resultPage;
		}

    	if(sizeof($ers)) {
	    	$err = implode("<br>", $ers);
	    	$this->messageManager->addError(__("%1<br>", $err));
	    	$this->_redirect('rewardpoints/invitation/loginmail');
    	}
	}
}
