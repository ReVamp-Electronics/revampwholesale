<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

class Sellproduct extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \MW\RewardPoints\Model\ProductsellpointFactory
     */
    protected $_sellpointFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \MW\RewardPoints\Model\ProductsellpointFactory $sellpointFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \MW\RewardPoints\Model\ProductsellpointFactory $sellpointFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_sellpointFactory = $sellpointFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        if (isset($row['custom_option']) && $row['custom_option']) {
            $id = $row['entity_id'] . "_" . $row['option_id'] . "_" . $row->getEntityTypeId();
        } elseif (isset($row['super_attribute']) && $row['super_attribute']) {
            $id = $row['option_id'] . "_" . $row['entity_id'] . "_" . $row['custom_attribute_id'];
        } else {
            $id = $row['entity_id'];
        }

        if (isset($row['super_attribute']) && $row['super_attribute']) {
            $name = 'mw_reward_point_sell_product[super_attribute_' . $id . ']';
        } else {
            $name = 'mw_reward_point_sell_product[mw_' . $id . ']';
        }

        if (isset($row['custom_option']) || isset($row['super_attribute'])) {
            $collection = $this->_sellpointFactory->create()->getCollection()
                ->addFieldToFilter('product_id', $row['entity_id'])
                ->addFieldToFilter('option_id', $row['option_id']);

            if (isset($row['custom_option']) && $row['custom_option']) {
                $collection->addFieldToFilter('option_type_id', $row['entity_type_id'])
                    ->addFieldToFilter('type_id', 'custom_option');
            } else {
                $collection->addFieldToFilter('option_type_id', $row['custom_attribute_id'])
                    ->addFieldToFilter('type_id', 'super_attribute');
            }
            $collection->getFirstItem();

            $value = $collection->getSellPoint();
        } else {
            $value = $row['mw_reward_point_sell_product'];
        }

        $result = '<input type="text" class="input-text validate-number" style="width: 80px !important" name="' . $name . '" value="' . $value . '" />';

        return $result;
    }
}
