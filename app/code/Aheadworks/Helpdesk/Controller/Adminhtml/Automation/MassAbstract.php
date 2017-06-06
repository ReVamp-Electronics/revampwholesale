<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Controller\Adminhtml\Automation;

/**
 * Class MassAbstract
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Automation
 */
abstract class MassAbstract extends \Aheadworks\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * Ticket Collection
     *
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Grid\Collection
     */
    protected $collection;

    /**
     * Massaction filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * Automation factory
     * @var \Aheadworks\Helpdesk\Model\AutomationFactory
     */
    protected $automationFactory;

    /**
     * Automation resource
     * @var \Aheadworks\Helpdesk\Model\ResourceModel\Automation
     */
    protected $automationResource;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Grid\Collection $collection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation\Grid\Collection $collection
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->collection = $collection;
        $this->filter = $filter;
        $this->automationFactory = $automationFactory;
        $this->automationResource = $automationResource;
    }

    /**
     * Mass update ticket(s) action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->collection = $this->filter->getCollection($this->collection);
        $paramCode = $this->getFilterParam();
        $paramValue = $this->getRequest()->getParam($paramCode);
        $count = 0;

        foreach ($this->collection->getItems() as $automation) {
            try {
                $automationModel = $this->automationFactory->create();
                $this->automationResource->load($automationModel, $automation->getId());
            } catch (\Exception $e) {
                $automationModel = null;
            }
            if ($automationModel && $automationModel->getId()) {
                $automationModel->setData($paramCode, $paramValue);
                $this->automationResource->save($automationModel);
                $count++;
            }
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been updated.', $count)
        );
        return $this->resultRedirectFactory->create()->setRefererUrl();
    }

    /**
     * Get filter param
     *
     * @return string
     */
    abstract protected function getFilterParam();
}
