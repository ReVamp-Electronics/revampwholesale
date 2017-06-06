<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Aheadworks\Rma\Model\Source\Request\Status;

/**
 * Class ConfirmShipping
 * @package Aheadworks\Rma\Controller\Guest
 */
class ConfirmShipping extends \Aheadworks\Rma\Controller\Guest
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $rmaRequest = $this->getRmaRequest();
            if ($this->isRequestValid($rmaRequest)) {
                $this->requestManager->setStatus($rmaRequest, Status::PACKAGE_SENT);
                $this->messageManager->addSuccessMessage(__('Request status has been successfully changed.'));
                return $resultRedirect->setPath('*/*/view', ['id' => $rmaRequest->getExternalLink()]);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while changing the request status.')
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}