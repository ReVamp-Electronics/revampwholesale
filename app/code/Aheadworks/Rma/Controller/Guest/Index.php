<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Controller\Guest;

/**
 * Class Index
 * @package Aheadworks\Rma\Controller\Guest
 */
class Index extends \Aheadworks\Rma\Controller\Guest
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        return $this->getResultPage(['title' => __('Request RMA')]);
    }
}