<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Model\Relation;

class ParentAttributeProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $options = null;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Amasty\CustomerAttributes\Helper\Collection
     */
    private $collectionHelper;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $collectionFactory,
        \Amasty\CustomerAttributes\Helper\Collection $collectionHelper
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->collectionHelper = $collectionHelper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $collection = $this->collectionFactory->create()
                ->addVisibleFilter();
            $collection = $this->collectionHelper->addFilters(
                $collection,
                'eav_attribute',
                [
                    "is_user_defined = 1",
                    "attribute_code != 'customer_activated' ",
                    "frontend_input in ('multiselect', 'select', 'multiselectimg', 'selectimg')"
                ]
            );

            $this->options = [];
            foreach ($collection as $attribute) {
                $label = $attribute->getFrontendLabel();
                if (!$attribute->getIsVisibleOnFront()) {
                    $label .= ' - ' . __('Not Visible');
                }
                $this->options[] = [
                    'value' => $attribute->getAttributeId(),
                    'label' => $label
                ];
            }
        }

        return $this->options;
    }

    /**
     * Get selected Attribute ID for default
     * used when no Attribute ID in data for load Attribute options
     *
     * @return array|false
     */
    public function getDefaultSelected()
    {
        if (count($this->toOptionArray())) {
            return current($this->toOptionArray());
        }
        return false;
    }
}
