<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package Aheadworks\Rma\Controller\Customer
 */
class View extends \Aheadworks\Rma\Controller\Customer
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $rmaRequest = $this->getRmaRequest();
            if ($this->isRequestValid($rmaRequest)) {
                $this->coreRegistry->register('aw_rma_request', $rmaRequest);
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->goBack();
        }
        return $this->getResultPage([
            'title' => __('Manage RMA Request %1', $rmaRequest->getIncrementId()),
            'link_back' => ['name' => 'customer.account.link.back', 'route_path' => 'aw_rma/customer']
        ]);
    }
}