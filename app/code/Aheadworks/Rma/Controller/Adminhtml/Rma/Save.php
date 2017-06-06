<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Aheadworks\Rma\Model\Status;
use Aheadworks\Rma\Model\Source\Request\Status as StatusSource;
use Magento\Backend\App\Action;

class Save extends \Aheadworks\Rma\Controller\Adminhtml\Rma
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * @var \Aheadworks\Rma\Model\RequestManager
     */
    private $requestManager;

    /**
     * @var \Aheadworks\Rma\Model\RequestFactory
     */
    protected $requestModelFactory;

    public function __construct(
        \Magento\Framework\Registry $registry,
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Aheadworks\Rma\Model\RequestFactory $requestModelFactory,
        \Aheadworks\Rma\Model\RequestManager $requestManager
    ) {
        $this->requestModelFactory = $requestModelFactory;
        $this->requestManager = $requestManager;
        $this->coreRegistry = $registry;
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
        $id = $this->getRequest()->getParam('request_id');
        $back = $this->getRequest()->getParam('back');
        if (!$id) {
            $this->messageManager->addError(__('Something went wrong while saving the request.'));
            return $resultRedirect->setPath('*/*/');
        }
        /** @var $request \Aheadworks\Rma\Model\Request */
        $request = $this->requestModelFactory->create()->load($id);
        if (isset($data['custom_fields'])) {
            $customFields = array_replace($request->getData('custom_fields'), $data['custom_fields']);
            $request->setData('custom_fields', $customFields);
        }
        if (isset($data['items'])) {
            $request->setData('items', $data['items']);
        }
        if ($status = $this->getRequest()->getParam('status')) {
            $request->setStatusId($status);
        }
        try {
            $request->save();
            if ($status) {//todo control before saving status(case status was changed from frontend before admin form submit)
                if ($status == StatusSource::CANCELED) {
                    $this->coreRegistry->register('aw_rma_cancel_by_admin', true);
                }
                $this->requestManager->notifyAboutStatusChange($request);
                $this->messageManager->addSuccess(__('Request status was successfully changed.'));
            }
            $this->messageManager->addSuccess(__('Request was successfully saved.'));
            if ($back == 'edit') {
                return $resultRedirect->setPath('*/*/' . $back, ['id' => $request->getId(), '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the request.'));
        }
        return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
    }
}