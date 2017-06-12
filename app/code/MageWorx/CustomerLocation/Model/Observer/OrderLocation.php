<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerLocation\Model\Observer;

/**
 * Customer Location observer
 */
class OrderLocation implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageWorx\CustomerLocation\Helper\Data
     */
    protected $helperData;
    
    /**
     * @var \MageWorx\CustomerLocation\Helper\Html
     */
    protected $helperHtml;
    
    /**
     * @var \MageWorx\GeoIP\Model\Geoip
     */
    protected $modelGeoip;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    
    /**
     * @param \MageWorx\CustomerLocation\Helper\Data $helperData
     * @param \MageWorx\CustomerLocation\Helper\Html $helperHtml
     * @param \MageWorx\GeoIP\Model\Geoip $modelGeoip
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \MageWorx\CustomerLocation\Helper\Data $helperData,
        \MageWorx\CustomerLocation\Helper\Html $helperHtml,
        \MageWorx\GeoIP\Model\Geoip $modelGeoip,
        \Magento\Framework\UrlInterface $url
    ) 
    {
        $this->helperData = $helperData;
        $this->helperHtml = $helperHtml;
        $this->modelGeoip = $modelGeoip;
        $this->url = $url;
    }
    
    /**
     * Adds GeoIP location html to order view
     *
     * @param Magento\Framework\Event\Observer $observer
     * @return MageWorx\CustomerLocation\Model\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helperData->isEnabledForOrders()) {
            $_order = null;
            $block = $observer->getEvent()->getBlock();
            $currentUrl = $this->url->getCurrentUrl();

            if ($block instanceof \Magento\Sales\Block\Adminhtml\Order\View\Info && strpos($currentUrl, '/sales/order/view/') !== false) {
                $_order = $block->getOrder();
            } elseif ($block instanceof \Magento\Shipping\Block\Adminhtml\Create\Form && strpos($currentUrl, 'admin/order_shipment/new/')) {
                $_order = $block->getOrder();
            } elseif ($block instanceof \Magento\Sales\Block\Adminhtml\Order\Invoice\Create\Form && strpos($currentUrl, 'sales/order_invoice/new') !== false) {
                $_order = $block->getOrder();
            }

            if (!is_null($_order)) {
                $ip = $_order->getRemoteIp();
                if (!$ip) {
                    return $this;
                }

                $geoIpObj = $this->modelGeoip->getLocation($ip);

                if ($geoIpObj->getCode()) {
                    $data = [
                                'geo_ip' => $geoIpObj,
                                'ip' => $ip,
                            ];
                    $obj = new \Magento\Framework\DataObject($data);
                    $block->getOrder()->setRemoteIp($this->helperHtml->getGeoIpHtml($obj));
                }
            }
        }
        return $this;
    }   
    
}
