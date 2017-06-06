<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source\Ticket;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\CollectionFactory;
use Aheadworks\Helpdesk\Model\ResourceModel\Department\Collection as DepartmentCollection;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentStoreLabelInterface;

/**
 * Class DepartmentFrontend
 * @package Aheadworks\Helpdesk\Model\Source\Ticket
 */
class DepartmentFrontend implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $departmentCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CollectionFactory $departmentCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $departmentCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->departmentCollectionFactory = $departmentCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        $store = $this->storeManager->getStore();

        /** @var DepartmentCollection $collection */
        $collection = $this->departmentCollectionFactory->create();
        $collection->addFieldToFilter(DepartmentInterface::IS_ENABLED, true);
        $collection->addFieldToFilter(DepartmentInterface::IS_VISIBLE, true);
        $collection->addWebsiteFilter($store->getWebsiteId());

        $departmentOptions = [];
        foreach ($collection as $item) {
            $label = false;
            if ($item->getStoreLabels()) {
                foreach ($item->getStoreLabels() as $storeLabel) {
                    if (
                        isset ($storeLabel[DepartmentStoreLabelInterface::STORE_ID]) &&
                        isset ($storeLabel[DepartmentStoreLabelInterface::LABEL]) &&
                        $storeLabel[DepartmentStoreLabelInterface::STORE_ID] == $store->getId()
                    ) {
                        $label = $storeLabel[DepartmentStoreLabelInterface::LABEL];
                        break;
                    }
                }
            }
            if (!$label) {
                $label = $item->getName();
            }

            $departmentOptions[] = [
                'value' => $item->getId(),
                'label' => $label,
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
