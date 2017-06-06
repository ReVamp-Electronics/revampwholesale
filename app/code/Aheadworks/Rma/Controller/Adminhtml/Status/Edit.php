<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Status;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Aheadworks\Rma\Controller\Adminhtml\Status
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Model\StatusFactory
     */
    protected $statusFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aheadworks\Rma\Model\StatusFactory $statusFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Aheadworks\Rma\Model\StatusFactory $statusFactory
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->coreRegistry = $coreRegistry;
        $this->statusFactory = $statusFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Aheadworks\Rma\Model\Status $status */
        $status = $this->statusFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $status->load($id);
        }
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $status->addData($data);
        }

        $this->coreRegistry->register('aw_rma_status', $status);

        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Rma::home');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit "%1" status', $status->getName()));
        return $resultPage;
    }
}