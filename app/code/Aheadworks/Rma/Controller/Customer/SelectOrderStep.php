<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Customer;

/**
 * Class SelectOrderStep
 * @package Aheadworks\Rma\Controller\Customer
 */
class SelectOrderStep extends \Aheadworks\Rma\Controller\Customer
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->getResultPage(['title' => __('New Return')]);
    }
}
