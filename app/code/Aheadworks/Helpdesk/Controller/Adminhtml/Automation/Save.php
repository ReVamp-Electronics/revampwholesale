<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Automation;

use Magento\Backend\App\Action;
use Magento\Framework\Message\Error;

/**
 * Class Save
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Automation
 */
class Save extends \Aheadworks\Helpdesk\Controller\Adminhtml\Automation
{
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
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory
     * @param \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Helpdesk\Model\AutomationFactory $automationFactory,
        \Aheadworks\Helpdesk\Model\ResourceModel\Automation $automationResource
    ) {
        $this->automationFactory = $automationFactory;
        $this->automationResource = $automationResource;
        parent::__construct($context, $resultPageFactory);

    }

    /**
     * Save action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            try {
                $id = $this->getRequest()->getParam('id');
                $automationModel = $this->automationFactory->create();
                if ($id) {
                    $this->automationResource->load($automationModel, $id);
                }
                $automationModel->setData($data);
                $this->automationResource->save($automationModel);
                $this->messageManager->addSuccess(__('Automation was successfully created.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->addSessionErrorMessages($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while creating the automation.'));
            }
            $data['id'] = $id;
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Add error messages
     *
     * @param $messages
     */
    protected function addSessionErrorMessages($messages)
    {
        $messages = (array)$messages;
        $session = $this->_getSession();

        $callback = function ($error) use ($session) {
            if (!$error instanceof Error) {
                $error = new Error($error);
            }
            $this->messageManager->addMessage($error);
        };
        array_walk_recursive($messages, $callback);
    }
}
