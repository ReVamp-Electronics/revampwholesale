<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Convert\DataObject;

/**
 * Class Websites
 * @package Aheadworks\Helpdesk\Model\Source
 */
class Websites implements OptionSourceInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var DataObject
     */
    private $objectConverter;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param DataObject $objectConverter
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        DataObject $objectConverter
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->objectConverter = $objectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $websites = [];
        foreach ($this->websiteRepository->getList() as $website) {
            if ($website->getId() != 0) {
                $websites[] = $website;
            }
        }
        return $this->objectConverter->toOptionArray($websites, 'id', 'name');
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        $options = $this->toOptionArray();
        $result = [];

        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
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
