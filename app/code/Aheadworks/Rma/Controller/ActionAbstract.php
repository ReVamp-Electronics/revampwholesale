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
 * Class ActionAbstract
 * @package Aheadworks\Rma\Controller
 */
abstract class ActionAbstract extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Aheadworks\Rma\Model\RequestFactory
     */
    protected $requestFactory;

    /**
     * @var \Aheadworks\Rma\Model\RequestManager
     */
    protected $requestManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Aheadworks\Rma\Model\RequestManager $requestManager
     * @param \Aheadworks\Rma\Model\RequestFactory $requestFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Aheadworks\Rma\Model\RequestManager $requestManager,
        \Aheadworks\Rma\Model\RequestFactory $requestFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->formKeyValidator = $formKeyValidator;
        $this->scopeConfig = $scopeConfig;
        $this->requestManager = $requestManager;
        $this->requestFactory = $requestFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function validateFormKey()
    {
        return $this->formKeyValidator->validate($this->getRequest());
    }

    /**
     * Get prepared result page
     *
     * @param array $params
     * @return \Magento\Framework\View\Result\Page
     */
    protected function getResultPage($params = [])
    {
        /** $resultPage @var \Magento\Framework\View\Result\Page */
        $resultPage = $this->resultPageFactory->create();
        if (isset($params['title'])) {
            $resultPage->getConfig()->getTitle()->set($params['title']);
        }
        if (isset($params['link_back'])) {
            /** @var \Magento\Customer\Block\Account\Dashboard $linkBack */
            $linkBack = $resultPage->getLayout()->getBlock($params['link_back']['name']);
            if ($linkBack) {
                $linkBack->setRefererUrl($this->_url->getUrl($params['link_back']['route_path']));
            }
        }
        return $resultPage;
    }

    /**
     * Get RMA request
     *
     * @return \Aheadworks\Rma\Model\Request|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getRmaRequest()
    {
        $rmaRequest = null;
        if ($rmaRequestId = $this->getRequest()->getParam('id')) {
            $rmaRequest = $this->loadRmaRequest($rmaRequestId);
        } else {
            throw new LocalizedException(__('External RMA ID isn\'t specified'));
        }
        return $rmaRequest;
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
        throw new LocalizedException(__('Request doesn\'t exists'));
        return null;
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
        throw new LocalizedException(__('Wrong request ID'));
        return false;
    }
}