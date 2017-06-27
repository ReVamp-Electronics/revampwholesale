<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Adminhtml\Customfield;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Aheadworks\Rma\Controller\Adminhtml\Customfield
 */
class Save extends \Aheadworks\Rma\Controller\Adminhtml\Customfield
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Aheadworks\Rma\Model\CustomFieldFactory
     */
    protected $customFieldFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Aheadworks\Rma\Model\CustomFieldFactory $customFieldFactory
     */
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
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Aheadworks\Rma\Model\CustomField $customField */
            $customField = $this->customFieldFactory->create();
            $id = $this->getRequest()->getParam('id');
            $customField
                ->load($id)
                ->setData($data)
            ;
            $back = $this->getRequest()->getParam('back');
            try {
                $customField->save();
                $this->messageManager->addSuccess(__('Custom field was successfully saved.'));
                $this->_getSession()->setFormData(false);
                if ($back == 'edit') {
                    return $resultRedirect->setPath('*/*/' . $back, ['id' => $customField->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the custom field.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}