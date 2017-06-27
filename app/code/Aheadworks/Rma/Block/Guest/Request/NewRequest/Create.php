<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Guest\Request\NewRequest;

/**
 * Class Create
 * @package Aheadworks\Rma\Block\Guest\Request\NewRequest
 */
class Create extends \Aheadworks\Rma\Block\Customer\Request\NewRequest\Step\CreateRequest
{
    /**
     * @var bool
     */
    protected $guestMode = true;

    /**
     * @return int
     */
    public function getCustomerEmail()
    {
        $requestData = $this->getRequestData();
        return $requestData['email'];
    }
}
