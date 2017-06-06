<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Guest;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;

/**
 * Class Reply
 * @package Aheadworks\Rma\Controller\Guest
 */
class Reply extends \Aheadworks\Rma\Controller\Guest
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->validateFormKey()) {
            return $resultRedirect->setPath('*/*/');
        }
        if ($data) {
            $data['owner_type'] = Owner::CUSTOMER_VALUE;
            try {
                $this->requestManager->reply($data);
                $this->messageManager->addSuccess(__('Comment successfully added.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while adding comment.'));
            }
            return $resultRedirect->setPath('*/*/view', ['id' => $data['request_id']]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}