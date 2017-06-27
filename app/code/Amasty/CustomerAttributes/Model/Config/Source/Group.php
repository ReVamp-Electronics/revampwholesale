<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Model\Config\Source;

class Group
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    private $collection;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Group\Collection $collection
    ) {
        $this->collection = $collection;
    }

    public function toOptionArray()
    {
        $groups = $this->collection->load()->toOptionArray();
        unset($groups[0]);
        return $groups;
    }
}
