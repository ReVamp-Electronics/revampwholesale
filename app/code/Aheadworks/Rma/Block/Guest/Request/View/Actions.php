<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Guest\Request\View;

/**
 * Class Actions
 * @package Aheadworks\Rma\Block\Guest\Request\View
 */
class Actions extends \Aheadworks\Rma\Block\Customer\Request\View\Actions
{
    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->getUrl('*/*/cancel', ['id' => $this->getRequestModel()->getExternalLink()]);
    }

    /**
     * @return string
     */
    public function getPrintLabelUrl()
    {
        return $this->getUrl('*/*/printLabel', ['id' => $this->getRequestModel()->getExternalLink()]);
    }

    /**
     * @return string
     */
    public function getConfirmShipping()
    {
        return $this->getUrl('*/*/confirmShipping', ['id' => $this->getRequestModel()->getExternalLink()]);
    }
}
