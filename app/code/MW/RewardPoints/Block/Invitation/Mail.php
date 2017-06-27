<?php

namespace MW\RewardPoints\Block\Invitation;

// Load openinviter library
if (!class_exists('openinviter')) {
    require_once __DIR__ . '/../../lib/openinviter.php';
}

class Mail extends \Magento\Framework\View\Element\Template
{
	public function getInviter()
	{
		$inviter = new \openinviter;
		$oiServices = $inviter->getPlugins();
		$emailBox = $this->getRequest()->getPost('email_box');
		$passwordBox = $this->getRequest()->getPost('password_box');
		$providerBox = $this->getRequest()->getPost('provider_box');

		$inviter->startPlugin($providerBox);
		$inviter->login($emailBox, $passwordBox);

		return $inviter;
	}

	public function getOiServices()
	{
		$inviter = new \openinviter;
		$oiServices = $inviter->getPlugins();

		return $oiServices;
	}
}
