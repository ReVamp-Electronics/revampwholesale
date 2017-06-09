<?php

namespace MW\RewardPoints\Controller\Invitation;

class Invite extends \MW\RewardPoints\Controller\Invitation
{
	public function execute()
	{
		$post = $this->getRequest()->getPost('email');
    	$post = trim($post, " ,");
    	$emails = explode(',', $post);

    	$error = [];
    	foreach ($emails as $email) {
    		$name = $email;
    		$_name = $this->getStringBetween($email,'"','"');
    		$_email = $this->getStringBetween($email,'<','>');

    		if ($_email!== false && $_name !== false) {
    			$email = $_email;
    			$name = $_name;
    		} else if ($_email!== false && $_name === false) {
    			if (strpos($email,'"') === false) {
    				$email = $_email;
    				$name = $email;
    			}
    		}
    		$email = trim($email);

	    	if (\Zend_Validate::is($email, 'EmailAddress')) {
	    		// Send email to friend
	    		$storeName = $this->_dataHelper->getStoreConfig('general/store_information/name');
				$template = parent::EMAIL_TO_RECIPIENT_TEMPLATE_XML_PATH;
				$customer = $this->_customerSession->getCustomer();
				$postObject = new \Magento\Framework\DataObject();
				$postObject->setSender($customer);
				$postObject->setMessage($this->getRequest()->getPost('message'));
				$postObject->setData('invitation_link', $this->_dataHelper->getLink($customer));
				$postObject->setStoreName($storeName);
				$this->_sendEmailTransaction($email, $name, $template, $postObject->getData());
			} else {
			   $error[] = $email;
			}
    	}

    	if (sizeof($error)) {
	    	$err = implode("<br>",$error);
	    	$this->messageManager->addError(
	    		__("These emails are invalid, the invitation message will not be sent to:<br>%1", $err)
	    	);
    	}

		$msg = __("Your email was sent success");
		if (sizeof($emails) > 1) {
			$msg = __("Your emails were sent successfully");
		}
		if (sizeof($emails) > sizeof($error)) {
			$this->messageManager->addSuccess($msg);
		}

    	$this->_redirect('rewardpoints/invitation/index');
	}
}
