<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerLocation\Helper;

/**
 * Customer Location HTML helper
 */
class Html extends \Magento\Framework\App\Helper\AbstractHelper
{    
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $elementTemplate;
    
    /**
     * @param \Magento\Framework\View\Element\Template $elementTemplate
     */
    public function __construct(
        \Magento\Framework\View\Element\Template $elementTemplate
    ) 
    {
        $this->elementTemplate = $elementTemplate;
    }

    /**
     * Return location html
     *
     * @param mixed $object
     * @return string
     */
    public function getGeoIpHtml($object)
    {
        $block = $this->elementTemplate->getLayout()
            ->createBlock('\Magento\Backend\Block\Template')
            ->setTemplate('MageWorx_CustomerLocation::customer-geoip.phtml')
            ->setData('item', $object)
            ->toHtml();

        return $block;
    }

}