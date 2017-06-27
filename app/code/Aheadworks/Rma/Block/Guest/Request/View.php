<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Guest\Request;

/**
 * Class View
 * @package Aheadworks\Rma\Block\Guest\Request
 */
class View extends \Aheadworks\Rma\Block\Customer\Request\View
{
    /**
     * @return int|string
     */
    public function getRequestIdentityValue()
    {
        return $this->getRequestModel()->getExternalLink();
    }
}
