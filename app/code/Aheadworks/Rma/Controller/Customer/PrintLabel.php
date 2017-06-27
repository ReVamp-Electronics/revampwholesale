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
 * Class PrintLabel
 * @package Aheadworks\Rma\Controller\Customer
 */
class PrintLabel extends \Aheadworks\Rma\Controller\Customer
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
            'title' => __('%1 - %2', $rmaRequest->getIncrementId(), $rmaRequest->getStatusFrontendLabel()),
            'link_back' => ['name' => 'customer.account.link.back', 'route_path' => 'aw_rma/customer']
        ]);
    }
}