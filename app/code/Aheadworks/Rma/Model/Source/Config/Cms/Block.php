<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Source\Config\Cms;

/**
 * Class Block
 * @package Aheadworks\Rma\Model\Source\Config\Cms
 */
class Block implements \Magento\Framework\Option\ArrayInterface
{
    const DONT_DISPLAY          = -1;

    const DONT_DISPLAY_LABEL    = 'Don\'t display';

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    private $blockCollection;

    /**
     * @var null|array
     */
    protected $optionArray = null;

    /**
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockCollectionFactory
    ) {
        $this->blockCollection = $blockCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->optionArray === null) {
            $this->optionArray = array_merge(
                [self::DONT_DISPLAY => __(self::DONT_DISPLAY_LABEL)],
                $this->blockCollection->toOptionArray()
            );
        }
        return $this->optionArray;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * @param int $value
     * @return null|\Magento\Framework\Phrase
     */
    public function getOptionLabelByValue($value)
    {
        $options = $this->getOptions();
        if (array_key_exists($value, $options)) {
            return $options[$value];
        }
        return null;
    }
}
