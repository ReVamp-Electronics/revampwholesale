<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Customer
 * @package Aheadworks\Rma\Controller
 */
abstract class Customer extends ActionAbstract
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Aheadworks\Rma\Model\RequestManager $requestManager
     * @param \Aheadworks\Rma\Model\RequestFactory $requestFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aheadworks\Rma\Model\RequestManager $requestManager,
        \Aheadworks\Rma\Model\RequestFactory $requestFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $coreRegistry,
            $formKeyValidator,
            $scopeConfig,
            $requestManager,
            $requestFactory
        );
        $this->customerSession = $customerSession;
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * Load RMA request
     *
     * @param int|string|\Aheadworks\Rma\Model\Request $request
     * @return \Aheadworks\Rma\Model\Request|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function loadRmaRequest($request)
    {
        return $this->requestManager->getRequestModel($request);
    }

    /**
     * RMA request validation
     *
     * @param \Aheadworks\Rma\Model\Request $rmaRequest
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isRequestValid($rmaRequest)
    {
        $result = false;
        if ($rmaRequest) {
            $sessionCustomerId = $this->customerSession->getCustomerId();
            $requestCustomerId = $rmaRequest->getCustomerId();
            if ($sessionCustomerId == $requestCustomerId) {
                $result = true;
            } else {
                throw new LocalizedException(__('Wrong request ID'));
            }
        }
        return $result;
    }
}