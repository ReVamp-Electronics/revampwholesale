<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Guest
 * @package Aheadworks\Rma\Controller
 */
abstract class Guest extends ActionAbstract
{
    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $allowGuestRma = (bool)$this->scopeConfig->getValue(
            'aw_rma/general/allow_guest_requests',
            ScopeInterface::SCOPE_STORE
        );
        if (!$allowGuestRma) {
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
        $resultRedirect->setUrl($this->_url->getUrl('aw_rma/guest'));
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
        return $this->requestManager->getRequestModelForGuest($request);
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
        return true;
    }
}