<?php

namespace MW\RewardPoints\Controller;

use Magento\Framework\App\Area;

abstract class Invitation extends \Magento\Framework\App\Action\Action
{
    const EMAIL_TO_RECIPIENT_TEMPLATE_XML_PATH  = 'rewardpoints/email_notifications/invitation_email';
    const XML_PATH_EMAIL_IDENTITY               = 'rewardpoints/email_notifications/email_sender';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MW\RewardPoints\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_dataHelper = $dataHelper;
    }

	/**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
    	// Check this module is enabled in frontend
		if ($this->_dataHelper->moduleEnabled() && $this->_customerSession->isLoggedIn()) {
			return parent::dispatch($request);
		} else {
			$this->_forward('noroute');
		}
    }

    /**
     * Get string between the first and the last character
     *
     * @param  string $string
     * @param  string $startCharacter
     * @param  string $endCharacter
     * @return string
     */
    protected function getStringBetween($string, $startCharacter, $endCharacter)
    {
        $startStringIndex = strpos($string,$startCharacter);
        if ($startStringIndex === false) {
            return false;
        }

        $startStringIndex ++;

        $endStringIndex = strpos($string, $endCharacter, $startStringIndex);
        if ($endStringIndex === false) {
            return false;
        }

        return substr($string, $startStringIndex, $endStringIndex - $startStringIndex);
    }

    /**
     * Send invitation email
     *
     * @param  string $emailTo
     * @param  string $name
     * @param  string $template
     * @param  array $data
     * @return void
     */
    protected function _sendEmailTransaction($emailTo, $name, $template, $data)
    {
        $data['subject'] = __('Reward Points Invitation');
        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $templateId = $this->_dataHelper->getStoreConfig($template, $store->getCode());
        $sender = $this->_dataHelper->getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getCode());
        $inlineTranslation = $this->_objectManager->get(
            'Magento\Framework\Translate\Inline\StateInterface'
        );
        $transportBuilder = $this->_objectManager->get(
            'Magento\Framework\Mail\Template\TransportBuilder'
        );

        try {
            $inlineTranslation->suspend();
            $transportBuilder->setTemplateIdentifier(
                $templateId
            )->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $store->getId()
                ]
            )->setTemplateVars(
                $data
            )->setFrom(
                [
                    'email' => $sender,
                    'name' => $data['store_name']
                ]
            )->addTo(
                $emailTo,
                $name
            );
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
            $inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Email can not send !"));
        }
    }
}
