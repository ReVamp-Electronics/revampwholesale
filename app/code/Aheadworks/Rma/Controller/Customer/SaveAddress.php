<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class SaveAddress
 * @package Aheadworks\Rma\Controller\Customer
 */
class SaveAddress extends \Aheadworks\Rma\Controller\Customer
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
            $rmaRequestId = $data['request_id'];
            unset($data['request_id']);
            unset($data['form_key']);
            try {
                $rmaRequest = $this->loadRmaRequest($rmaRequestId);
                if ($this->isRequestValid($rmaRequest)) {
                    $this->requestManager->updatePrintLabel($rmaRequest, $data);
                    $this->messageManager->addSuccessMessage(__('Contact information has been successfully saved.'));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the contact information.')
                );
            }
            return $resultRedirect->setPath('*/*/view', ['id' => $rmaRequest->getId()]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}