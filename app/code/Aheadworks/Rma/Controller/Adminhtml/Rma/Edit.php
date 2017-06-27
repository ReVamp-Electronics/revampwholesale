<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action;

class Edit extends \Aheadworks\Rma\Controller\Adminhtml\Rma
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Aheadworks\Rma\Model\RequestFactory
     */
    protected $requestModelFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Rma\Model\RequestFactory $requestModelFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->requestModelFactory = $requestModelFactory;
        $this->coreRegistry = $registry;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var $request \Aheadworks\Rma\Model\Request */
        $request = $this->requestModelFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $request->load($id);
        }
        if (!$id || !$request->getId()) {
            $this->messageManager->addError(__('This request no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/*');
        }

        $this->coreRegistry->register('aw_rma_request', $request);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Rma::manage_rma');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Request'));
        return $resultPage;
    }
}