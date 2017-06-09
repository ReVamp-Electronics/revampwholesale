<?php

namespace MW\RewardPoints\Block\Adminhtml\Renderer;

class Rewardpoints extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
            $name = 'reward_point_product[super_attribute_' . $id . ']';
        } else {
            $name = 'reward_point_product[mw_' . $id . ']';
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

            $value = $collection->getEarnPoint();
        } else {
            $value = $row['reward_point_product'];
        }

		$result = '<input type="text" style="width: 65px;" class="input-text validate-number" name="'.$name.'" value="'.$value.'" />';

    	return $result;
    }
}
