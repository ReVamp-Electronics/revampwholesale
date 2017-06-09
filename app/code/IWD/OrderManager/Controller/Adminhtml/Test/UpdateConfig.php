<?php

namespace IWD\OrderManager\Controller\Adminhtml\Test;

use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class UpdateConfig
 * @package IWD\OrderManager\Controller\Adminhtml\Test
 */
class UpdateConfig extends Action
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $configResource;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\ResourceModel\Config $configResource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\ResourceModel\Config $configResource
    ) {
        $this->configResource = $configResource;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        /*
        Tax Calculation Method Based On
        algorithm
         - UNIT_BASE_CALCULATION
         - ROW_BASE_CALCULATION
         - TOTAL_BASE_CALCULATION

        Tax Calculation Based On
        based_on
         - shipping
         - billing
         - origin

        Catalog Prices
        price_includes_tax
         - 0 Excluding Tax
         - 1 Including Tax

        Shipping Prices
        shipping_includes_tax
         - 0 Excluding Tax
         - 1 Including Tax

        Apply Customer Tax
        apply_after_discount
         - 0 Excluding Tax
         - 1 Including Tax

        Apply Discount On Prices
        discount_tax
         - 0 Excluding Tax
         - 1 Including Tax

        Apply Tax On
        apply_tax_on
         - 0 Custom price if available
         - 1 Original price only

        Enable Cross Border Trade
        cross_border_trade_enabled
         - 1 Yes
         - 0 No
        */

        $params = [
            'algorithm',
            'based_on',
            'price_includes_tax',
            'shipping_includes_tax',
            'apply_after_discount',
            'discount_tax',
            'apply_tax_on',
            'cross_border_trade_enabled'
        ];

        foreach ($params as $param) {
            $val = $this->getRequest()->getParam($param, null);
            if ($val !== null) {
                $this->configResource->saveConfig(
                    'tax/calculation/' . $param,
                    $val,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    0
                );
            }
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
