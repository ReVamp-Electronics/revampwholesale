<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Customfield;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Aheadworks\Rma\Controller\Adminhtml\Customfield
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    protected $customFieldFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->coreRegistry = $coreRegistry;
        $this->customFieldFactory = $customFieldFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Aheadworks\Rma\Model\CustomField $customField */
        $customField = $this->customFieldFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $customField->load($id);
        }
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $customField->addData($data);
        }

        $this->coreRegistry->register('aw_rma_custom_field', $customField);

        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Aheadworks_Rma::home');
        $resultPage->getConfig()->getTitle()
            ->prepend($customField->getId() ? __('Edit "%1" custom field', $customField->getName()) : 'New custom field')
        ;
        return $resultPage;
    }
}