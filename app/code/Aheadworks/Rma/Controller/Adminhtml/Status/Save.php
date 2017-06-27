<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Status;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Aheadworks\Rma\Controller\Adminhtml\Status
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Model\StatusFactory
     */
    protected $statusFactory;

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
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Aheadworks\Rma\Model\Status $status */
            $status = $this->statusFactory->create();
            $id = $this->getRequest()->getParam('id');
            $status
                ->load($id)
                ->setData($data)
            ;
            $back = $this->getRequest()->getParam('back');
            try {
                $status->save();
                $this->messageManager->addSuccess(__('Status was successfully saved.'));
                $this->_getSession()->setFormData(false);
                if ($back == 'edit') {
                    return $resultRedirect->setPath('*/*/' . $back, ['id' => $status->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the status.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}