<?php

namespace MW\RewardPoints\Controller;

abstract class Checkout extends \Magento\Framework\App\Action\Action
{
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
}
