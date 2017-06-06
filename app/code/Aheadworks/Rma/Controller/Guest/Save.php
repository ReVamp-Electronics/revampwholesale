<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Class Save
 * @package Aheadworks\Rma\Controller\Guest
 */
class Save extends \Aheadworks\Rma\Controller\Guest
{
    /**
     * @return $this
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->validateFormKey()) {
            return $resultRedirect->setPath('*/*/');
        }
        if ($data) {
            unset($data['form_key']);
            try {
                $requestModel = $this->requestManager->create($data, true);
                $this->messageManager->addSuccess(__('Return has been successfully created.'));
                return $resultRedirect->setPath('*/*/view', ['id' => $requestModel->getExternalLink()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while creating the return.'));
            }
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        return $resultRedirect->setPath('*/*/');
    }
}