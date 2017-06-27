<?php

namespace MW\RewardPoints\Controller;

abstract class Rewardpoints extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \MW\RewardPoints\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * List of actions that are allowed for not authorized users
     *
     * @var string[]
     */
    protected $openActions = [
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost',
        'confirm',
        'confirmation',
    ];

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MW\RewardPoints\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->_dataHelper = $dataHelper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return string[]
     */
    protected function getAllowedActions()
    {
        return $this->openActions;
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
		if (!$this->_dataHelper->moduleEnabled()) {
            $this->_forward('noroute');
        }

        // Check customer authentication for some actions
        $action = strtolower($this->getRequest()->getActionName());
        $pattern = '/^(' . implode('|', $this->getAllowedActions()) . ')$/i';
        if (!preg_match($pattern, $action)) {
            if (!$this->_customerSession->isLoggedIn()) {
                $this->_forward('noroute');
            }
        } else {
            $this->_customerSession->setNoReferer(true);
        }

        $this->_customerSession->unsNoReferer(false);

        return parent::dispatch($request);
    }
}
