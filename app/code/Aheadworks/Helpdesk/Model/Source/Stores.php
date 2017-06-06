<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/


namespace Aheadworks\Helpdesk\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Ui\Component\Listing\Column\Store\Options;

/**
 * Class Stores
 * @package Aheadworks\Helpdesk\Model\Source
 */
class Stores implements OptionSourceInterface
{
    /**
     * Store options
     * @var Options
     */
    private $storeOptions;

    /**
     * Stores constructor.
     * @param Options $storeOptions
     */
    public function __construct(Options $storeOptions)
    {
        $this->storeOptions = $storeOptions;
    }

    /**
     * To option array
     * @return array
     */
    public function toOptionArray()
    {
        return $this->storeOptions->toOptionArray();
    }
}
