<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Automation;

/**
 * Class Delete
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Automation
 */
class Delete extends \Aheadworks\Helpdesk\Controller\Adminhtml\Automation
{
    /**
     * Automation model factory
     * @var \Aheadworks\Helpdesk\Model\AutomationFactory
     */
    protected $automationFactory;

    /**
     * Automation resource model
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation
     */
    protected $automationResource;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->automationFactory = $automationFactory;
        $this->automationResource = $automationResource;
    }

    /**
     * Delete Automation
     *
     * @return $this
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        $automationModel = $this->automationFactory->create();
        if ($id) {
            $this->automationResource->load($automationModel, $id);
            if ($automationModel->getId()) {
                try {
                    $automationModel->delete();
                    $this->messageManager->addSuccessMessage(__('Automation was successfully deleted.'));
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    return $resultRedirect->setPath('*/automation/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
            }
        }
        return $resultRedirect->setPath('*/automation/');
    }
}
