<?php

namespace MW\RewardPoints\Controller\Invitation;

class Inviteajax extends \MW\RewardPoints\Controller\Invitation
{
	public function execute()
	{
		$url = trim($this->getRequest()->getParam('url_link'));
    	$email = trim($this->getRequest()->getParam('email'));
    	$message = trim($this->getRequest()->getParam('message'));
    	if ($email == '' || $message == '') {
    		$mw_email = 1;
    		$mw_message = 1;
    		if ($email == '') {
    			$mw_email = 0;
    		}
    		if ($message == '') {
    			$mw_message = 0;
    		}
			$jsonData = [
				'message' => $mw_message,
				'email' => $mw_email,
				'error' => 0,
				'success' => 0
			];

			echo json_encode($jsonData);
			exit;
    	}

    	$email = trim($email,' ,');
    	$emails = explode(',', $email);
    	$error = [];
    	foreach ($emails as $email) {
    		$name = $email;
    		$_name = $this->getStringBetween($email,'"','"');
    		$_email = $this->getStringBetween($email,'<','>');

    		if ($_email !== false && $_name !== false) {
    			$email = $_email;
    			$name = $_name;
    		} else if ($_email !== false && $_name === false) {
    			if (strpos($email, '"') === false) {
    				$email = $_email;
    				$name = $email;
    			}
    		}
    		$email = trim($email);

	    	if (\Zend_Validate::is($email, 'EmailAddress')) {
	    		// Send email to friend
	    		$storeName = $this->_dataHelper->getStoreConfig('general/store_information/name');
				$template = parent::EMAIL_TO_RECIPIENT_TEMPLATE_XML_PATH;
				$postObject = new \Magento\Framework\DataObject();
				$customer = $this->_customerSession->getCustomer();
				$postObject->setSender($customer);
				$postObject->setMessage($message);
				$postObject->setData('invitation_link', $url);
				$postObject->setStoreName($storeName);
				$this->_sendEmailTransaction($email, $name, $template, $postObject->getData());
			} else {
			   $error[] = $email;
			}
    	}

    	if (sizeof($error)) {
    		$err = implode('<br>', $error);
	    	$mw_error = __("These emails are invalid, the invitation message will not be sent to:<br>%1",$err);
			$jsonData = [
				'message' => 1,
				'email' => 1,
				'error' => $mw_error,
				'success' => 0
			];

			echo json_encode($jsonData);
			exit;
    	}

    	$msg = 1;
		if (sizeof($emails) > 1) {
			$msg = 2;
		}
    	if (sizeof($emails) > sizeof($error)) {
			$jsonData = [
				'message' => 1,
				'email' => 1,
				'error' => 0,
				'success' => $msg
			];

			echo json_encode($jsonData);
			exit;
    	}
	}
}
