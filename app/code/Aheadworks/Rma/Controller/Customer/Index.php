<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Customer;

/**
 * Class Index
 * @package Aheadworks\Rma\Controller\Customer
 */
class Index extends \Aheadworks\Rma\Controller\Customer
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->getResultPage(['title' => __('My Returns')]);
    }
}