<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Guest\Request\View;

/**
 * Class Address
 * @package Aheadworks\Rma\Block\Guest\Request\View
 */
class Address extends \Aheadworks\Rma\Block\Customer\Request\View\Address
{
    /**
     * @return int|string
     */
    public function getRequestIdentityValue()
    {
        return $this->getRequestModel()->getExternalLink();
    }
}
