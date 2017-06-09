<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Model;

use Magento\Framework\Model\AbstractModel;

class Method extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Amasty\ShippingTableRates\Model\ResourceModel\Method');
    }

    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Model\Context $context
    )
    {
        parent::__construct($context, $coreRegistry);
    }

    public function massChangeStatus($ids, $status)
    {
        foreach ($ids as $id) {
            $model = $this->load($id);
            $model->setIsActive($status);
            $model->save();
        }
        return $this;
    }

    public function getFreeTypes()
    {
        $result = array();
        $freeTypesString = trim($this->getData('free_types'),',');
        if ($freeTypesString) {
            $result = explode(',', $freeTypesString);
        }
        return $result;
    }
}
