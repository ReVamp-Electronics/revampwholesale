<?php

namespace IWD\SalesRep\Controller\Customer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AuthorizationException;

/**
 * Class LogoutToParent
 * @package IWD\SalesRep\Controller\Customer
 */
class LogoutToParent extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \IWD\SalesRep\Helper\Data
     */
    private $salesrepHelper;

    /**
     * LogoutToParent constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \IWD\SalesRep\Helper\Data $salesrepHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \IWD\SalesRep\Helper\Data $salesrepHelper
    ) {
        $this->salesrepHelper = $salesrepHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $res = $this->salesrepHelper->returnToParentAccount();
            if ($res === null) {
                $this->messageManager->addErrorMessage('Can not return to initial account');
            } elseif ($res) {
                $this->messageManager->addSuccessMessage('Successfully return to salesrep account');
            } else {
                throw new AuthorizationException(__('Fail to switch to initial account'));
            }

            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Error during re-authorization');
        }

        return $resultRedirect;
    }
}
