<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block;

/**
 * Class BackLink
 * @package Aheadworks\Rma\Block
 */
class BackLink extends \Magento\Framework\View\Element\Template
{
    /**
     * Get back Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/');
    }
}