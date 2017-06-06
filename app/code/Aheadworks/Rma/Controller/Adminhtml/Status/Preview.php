<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Status;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Class Preview
 * @package Aheadworks\Rma\Controller\Adminhtml\Status
 */
class Preview extends \Aheadworks\Rma\Controller\Adminhtml\Status
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $registry;
        $this->formKey = $formKey;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $this->_getSession()->setPreviewData($this->getRequest()->getPostValue());
        } else {
            $this->_view->loadLayout(['aw_rma_admin_preview'], true, true, false);
            $data = $this->_getSession()->getPreviewData();
            if (
                empty($data) ||
                !isset($data['form_key']) || $data['form_key'] !== $this->formKey->getFormKey()
            ) {
                /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
                $resultForward = $this->resultForwardFactory->create();
                return $resultForward->forward('noroute');
            } else {
                $this->coreRegistry->register('template_id', $data['template']);
                $this->coreRegistry->register('status_id', $data['status']);
                $this->coreRegistry->register('to_admin', (bool)$data['to_admin']);
                $this->_view->renderLayout();
            }
        }
    }
}
