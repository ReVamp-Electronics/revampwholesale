<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;

/**
 * Class Departments
 * @package Aheadworks\Helpdesk\Model\Source\Ticket
 */
class Departments implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $departmentCollectionFactory;

    /**
     * @param CollectionFactory $departmentCollectionFactory
     */
    public function __construct(
        CollectionFactory $departmentCollectionFactory
    ) {
        $this->departmentCollectionFactory = $departmentCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /** @var DepartmentCollection $collection */
        $collection = $this->departmentCollectionFactory->create();

        $departmentOptions = [];
        foreach ($collection as $item) {
            $departmentOptions[] = [
                'value' => $item->getId(),
                'label' => $item->getName(),
            ];
        }
        return $departmentOptions;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $optionsArray = $this->toOptionArray();
        $options = [];
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * Get option by value
     *
     * @param int $value
     * @return string|null
     */
    public function getOptionByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
