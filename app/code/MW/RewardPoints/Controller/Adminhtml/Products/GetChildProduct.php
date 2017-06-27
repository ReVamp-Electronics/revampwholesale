<?php

namespace MW\RewardPoints\Controller\Adminhtml\Products;

class GetChildProduct extends \MW\RewardPoints\Controller\Adminhtml\Products
{
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['type_id'])) {
            switch ($params['type_id']) {
                case 'bundle':
                    $product = $this->_objectManager->get(
                        'Magento\Catalog\Model\Product'
                    )->load($params['product_id']);
                    $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
                        $product->getTypeInstance(true)->getOptionsIds($product),
                        $product
                    );
                    foreach ($selectionCollection as $option) {
                        $product = $this->_objectManager->get(
                            'Magento\Catalog\Model\Product'
                        )->load($option->getProductId());
                    }
                    break;
            }
        }
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MW_RewardPoints::products_sell');
    }
}
