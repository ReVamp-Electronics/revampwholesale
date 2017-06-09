<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Block\Onepage;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;

class LayoutProcessor implements LayoutProcessorInterface
{
    protected $_moduleManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    )
    {
        $this->_moduleManager = $moduleManager;
    }

    public function process($jsLayout)
    {
        if (!$this->_moduleManager->isEnabled('Magestore_OneStepCheckout')) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['template'] = 'Amasty_ShippingTableRates/shipping';
        }

        return $jsLayout;
    }
}
