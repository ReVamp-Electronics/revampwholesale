<?php
/*////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\  Customer Approve/Disapprove 2.0 \\\\\\\\\\\\\\\\\\\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ NOTICE OF LICENSE\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                                                                   ///////
 \\\\\\\ This source file is subject to the Open Software License (OSL 3.0)\\\\\\\
 ///////   that is bundled with this package in the file LICENSE.txt.      ///////
 \\\\\\\   It is also available through the world-wide-web at this URL:    \\\\\\\
 ///////          http://opensource.org/licenses/osl-3.0.php               ///////
 \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
 ///////                      * @category   EH                            ///////
 \\\\\\\                      * @package    EH_CustomerApprove             \\\\\\\
 ///////    * @author     Extensionhut <info@extensionhut.com>             ///////
 \\\\\\\                                                                   \\\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 \\\\\\* @copyright  Copyright 2016 © www.extensionhut.com All right reserved\\\\\
 /////////////////////////////////////////////////////////////////////////////////
 */

namespace EH\CustomerApprove\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Helper\Page as CmsHelper;
use Magento\Framework\Mail\Template\TransportBuilder;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

	/**
     * system config options
     */
    const EH_CA_ENABLED = 'customerApprove/general/enabled';
	const EH_CA_AUTO_APPROVE = 'customerApprove/general/auto_approve';
	const EH_CA_WELCOME_EMAIL = 'customerApprove/general/welcome_email';

    const EH_CA_REDIRECT_ENABLED 	= 'customerApprove/redirect/enabled';
	const EH_CA_REDIRECT_CMS_PAGE 	= 'customerApprove/redirect/cms_page';
	const EH_CA_REDIRECT_USE_CUSTOM = 'customerApprove/redirect/use_custom_url';
	const EH_CA_REDIRECT_CUSTOM_URL = 'customerApprove/redirect/custom_url';

	const EH_CA_ERROR_MSG_ENABLED	= 'customerApprove/error_msg/enabled';
	const EH_CA_ERROR_MSG_TEXT		= 'customerApprove/error_msg/text';
	
	const APPROVAL_EMAIL_ENABLED		= 'customerApprove/email/enabled';
	const APPROVAL_EMAIL_SENDER		= 'customerApprove/email/identity';
	const APPROVAL_EMAIL_TEMPLATE = 'customerApprove/email/template';
	
	const ADMIN_EMAIL_ENABLED		= 'customerApprove/admin_notification/enabled';
	const ADMIN_EMAIL_SENDER		= 'customerApprove/admin_notification/identity';
	const ADMIN_EMAIL_TEMPLATE = 'customerApprove/admin_notification/template';
	const ADMIN_EMAIL_RECIPIENTS = 'customerApprove/admin_notification/recipients';
	
	const CUSTOMER_GROUP_ENABLED = 'customerApprove/customer_group/enabled';
	const CUSTOMER_GROUP = 'customerApprove/customer_group/customer_group';
	
	/**
	 * Whether or not the extension is enabled
	 *
	 * @var boolean
	 */
	protected $_enabled;

	/**
	 * Auto approve new customers, those sign-up to store
	 *
	 * @var boolean
	 */
	protected $_autoApprove;
	
	/**
	 * Welcome email for new customers
	 *
	 * @var boolean
	 */
	protected $_welcomeEmail;
	
	/**
	 * Customer group restrictions enabled or not
	 *
	 * @var boolean
	 */
	protected $_customerGroupEnabled;
	
	/**
	 * Customer group
	 *
	 * @var string
	 */
	protected $_customerGroup;
	
	/**
	 * Approval email for new customers
	 *
	 * @var boolean
	 */
	protected $_approvalEmail;
	
	/**
	 * Approval email sender for new customers
	 *
	 * @var string
	 */
	protected $_approvalEmailSender;
	
	/**
	 * Approval email template for new customers
	 *
	 * @var string
	 */
	protected $_approvalEmailTemplate;
	
	/**
	 * Admin email for new customers
	 *
	 * @var boolean
	 */
	protected $_adminEmail;
	
	/**
	 * Admin email sender for new customers
	 *
	 * @var string
	 */
	protected $_adminEmailSender;
	
	/**
	 * Admin email template for new customers
	 *
	 * @var string
	 */
	protected $_adminEmailTemplate;
	
	/**
	 * Admin email recipients for new customers
	 *
	 * @var string
	 */
	protected $_adminEmailRecipients;

	/**
	 * Whether or not error messages is enabled
	 *
	 * @var boolean
	 */
	protected $_errorMsgEnabled;

	/**
	 * Error message text
	 *
	 * @var string
	 */
	protected $_errorMsgText;

	/**
	 * Whether or not redirect is enabled
	 *
	 * @var boolean
	 */
	protected $_redirectEnabled;

	/**
	 * Store id
	 *
	 * @var int
	 */
	protected $_storeId;

	/**
	 * Redirect URL for unapproved customers attempting to sign in
	 *
	 * @var string
	 */
	protected $_redirectURL;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $cmsPageHelper;
    
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $emailTemplateFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CmsHelper $cmsPageHelper,
        TransportBuilder $emailTemplateFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cmsPageHelper = $cmsPageHelper;
        $this->emailTemplateFactory = $emailTemplateFactory;
    }



	/**
	 * Retrieve whether or not the extension is enabled
	 *
	 * @return boolean
	 */
	public function getIsEnabled()
	{
		if(is_null($this->_enabled)) {
			$this->_enabled = intval($this->scopeConfig->getValue(self::EH_CA_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_enabled;
	}

	/**
	 * Retrieve the auto approve setting
	 *
	 * @return boolean
	 */
	public function getIsAutoApprove()
	{
		if(is_null($this->_autoApprove)) {
			$this->_autoApprove = intval($this->scopeConfig->getValue(self::EH_CA_AUTO_APPROVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_autoApprove;
	}
	
	/**
	 * Retrieve the welcome email setting
	 *
	 * @return boolean
	 */
	public function getIsWelcomeEmail()
	{
		if(is_null($this->_welcomeEmail)) {
			$this->_welcomeEmail = intval($this->scopeConfig->getValue(self::EH_CA_WELCOME_EMAIL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_welcomeEmail;
	}
	
	/**
	 * Retrieve the customer group settings
	 *
	 * @return boolean
	 */
	public function getIsCustomerGroups()
	{
		if(is_null($this->_customerGroupEnabled)) {
			$this->_customerGroupEnabled = intval($this->scopeConfig->getValue(self::CUSTOMER_GROUP_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_customerGroupEnabled;
	}
	
	/**
	 * Retrieve the customer groups
	 *
	 * @return string,boolean
	 */
	public function getCustomerGroups()
	{
		if(!$this->getIsCustomerGroups()) {
			return false;
		}
		if(is_null($this->_customerGroup)) {
			$this->_customerGroup = $this->scopeConfig->getValue(self::CUSTOMER_GROUP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
		}
		return $this->_customerGroup;
	}
	
	/**
	 * Retrieve the approval email setting
	 *
	 * @return boolean
	 */
	public function getIsApprovalEmail()
	{
		if(is_null($this->_approvalEmail)) {
			$this->_approvalEmail = intval($this->scopeConfig->getValue(self::APPROVAL_EMAIL_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_approvalEmail;
	}
	
	/**
	 * Retrieve the approval email setting
	 *
	 * @return string
	 */
	public function getApprovalEmailSender()
	{
		if(is_null($this->_approvalEmailSender)) {
			$this->_approvalEmailSender = $this->scopeConfig->getValue(self::APPROVAL_EMAIL_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
		}
		return $this->_approvalEmailSender;
	}
	
	/**
	 * Retrieve the approval email template
	 *
	 * @return string
	 */
	public function getApprovalEmailTemplate()
	{
		if(is_null($this->_approvalEmailTemplate)) {
			$this->_approvalEmailTemplate = $this->scopeConfig->getValue(self::APPROVAL_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
		}
		return $this->_approvalEmailTemplate;
	}
	
	/**
	 * Retrieve the admin email setting
	 *
	 * @return boolean
	 */
	public function getIsAdminEmail()
	{
		if(is_null($this->_adminEmail)) {
			$this->_adminEmail = intval($this->scopeConfig->getValue(self::ADMIN_EMAIL_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_adminEmail;
	}
	
	/**
	 * Retrieve the admin email sender
	 *
	 * @return string
	 */
	public function getAdminEmailSender()
	{
		if(is_null($this->_adminEmailSender)) {
			$this->_adminEmailSender = $this->scopeConfig->getValue(self::ADMIN_EMAIL_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
		}
		return $this->_adminEmailSender;
	}
	
	/**
	 * Retrieve the admin email template
	 *
	 * @return string
	 */
	public function getAdminEmailTemplate()
	{
		if(is_null($this->_adminEmailTemplate)) {
			$this->_adminEmailTemplate = $this->scopeConfig->getValue(self::ADMIN_EMAIL_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
		}
		return $this->_adminEmailTemplate;
	}
	
	/**
	 * Retrieve the admin email recipients
	 *
	 * @return string
	 */
	public function getAdminEmailRecipients()
	{
		if(is_null($this->_adminEmailRecipients)) {
			$this->_adminEmailRecipients = $this->scopeConfig->getValue(self::ADMIN_EMAIL_RECIPIENTS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
		}
		return $this->_adminEmailRecipients;
	}
	

	/**
	 * Get current store id
	 *
	 * @return int
	 */
	public function getStoreId()
	{
		if(is_null($this->_storeId)) {
			$this->_storeId = intval($this->storeManager->getStore()->getId());
		}

		return $this->_storeId;
	}

	/**
	 * Get whether or not error messages is enabled
	 *
	 * @return boolean
	 */
	public function getErrorMsgEnabled()
	{
		if(is_null($this->_errorMsgEnabled)) {
			$this->_errorMsgEnabled = intval($this->scopeConfig->getValue(self::EH_CA_ERROR_MSG_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_errorMsgEnabled;
	}

	/**
	 * Error message to be displayed, if any
	 *
	 * @return string
	 */
	public function getErrorMsgText()
	{
		if(is_null($this->_errorMsgText)) {
			$this->_errorMsgText = $this->scopeConfig->getValue(self::EH_CA_ERROR_MSG_TEXT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId());
		}
		return $this->_errorMsgText;
	}

	/**
	 * Get whether or not redirection is enabled
	 *
	 * @return boolean
	 */
	public function getRedirectEnabled()
	{
		if(is_null($this->_redirectEnabled)) {
			$this->_redirectEnabled = intval($this->scopeConfig->getValue(self::EH_CA_REDIRECT_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreId()))==1 ? true : false;
		}
		return $this->_redirectEnabled;
	}

	/**
	 * Retrieve redirection URL for unapproved customers
	 *
	 * @return string
	 */
	public function getRedirectUrl()
	{
		if(is_null($this->_redirectURL)) {
			// get store id
			$storeId = $this->getStoreId();

			if ($this->getRedirectEnabled()) {
				// check if we should use a custom URL or CMS page
				$useCustomUrl = intval($this->scopeConfig->getValue(self::EH_CA_REDIRECT_USE_CUSTOM, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId))==1 ? true : false;

				if ($useCustomUrl) {
					$this->_redirectURL = $this->scopeConfig->getValue(self::EH_CA_REDIRECT_CUSTOM_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
				}
				else {
					// get CMS page identifier
					$pageId = $this->scopeConfig->getValue(self::EH_CA_REDIRECT_CMS_PAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

					if (!empty($pageId)) {
						// check if id includes a delimiter
						$delPos = strrpos($pageId, '|');

						// get page id by delimiter position
						if ($delPos) {
							$pageId = substr($pageId, 0, $delPos);
						}

						// retrieve redirect URL
						$this->_redirectURL = $this->cmsPageHelper->getPageUrl($pageId);
					}
				}
			}
		}

		return $this->_redirectURL;
	}
	
	/**
	 * Send approval email
	 *
	 */
	public function sendApprovalEmail($customer)
	{
		if($this->getIsApprovalEmail() && $this->getIsEnabled()) {
			$this->_sendEmailTemplate(
				$customer->getStoreId(),
				$this->getApprovalEmailTemplate(), 
				$this->getApprovalEmailSender(), 
				[
					'customer_name' => $customer->getFirstName()." ".$customer->getLastName()
				], 
				$customer->getEmail(), $customer->getFirstName()
			);
		}
		return $this;
	}
	
	/**
	 * Send admin notification email
	 *
	 */
	public function sendAdminNotificationEmail($customer)
	{
		if($this->getIsAdminEmail() && $this->getIsEnabled()) {
			$adminEmailRecipients = explode(',',$this->getAdminEmailRecipients());
			foreach($adminEmailRecipients as $adminEmailRecipient) {
				$this->_sendEmailTemplate(
					$customer->getStoreId(),
					$this->getAdminEmailTemplate(), 
					$this->getAdminEmailSender(), 
					[
						'customer_name' => $customer->getFirstName()." ".$customer->getLastName(),
						'customer_email' => $customer->getEmail()
					], 
					$adminEmailRecipient
				);
			}
		}
		return $this;
	}
	
	/**
	 * Send email template
	 *
	 */
	private function _sendEmailTemplate($storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID, $template, $sender, $templateParams = [], $recipient)
	{
        /** @var \Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->emailTemplateFactory->setTemplateIdentifier($template)
        ->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            $templateParams
        )->setFrom(
            $sender
        )->addTo(
            $recipient
        )->getTransport();
        $transport->sendMessage();

        return $this;
    }

}
