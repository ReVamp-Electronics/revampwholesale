<?php

namespace IWD\OrderManager\Block\Adminhtml\Shipment\View;

use IWD\OrderManager\Model\Shipment\Shipment;
use Magento\Backend\Block\Widget\Container;

/**
 * Class Toolbar
 * @package IWD\OrderManager\Block\Adminhtml\Shipment\View
 */
class Toolbar extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var Shipment
     */
    private $shipment;

    /**
     * Toolbar constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Shipment $shipment
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        Shipment $shipment,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->shipment = $shipment;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->isAllowDeleteShipment()) {
            $this->addDeleteButton();
        }
    }

    /**
     * @return void
     */
    protected function addDeleteButton()
    {
        $message = __('Are you sure you want to DELETE an shipment?');
        $url = $this->getDeleteUrl();
        $this->addButton(
            'iwd_shipment_delete',
            [
                'label'   => 'Delete',
                'class'   => 'delete',
                'onclick' => "confirmSetLocation('{$message}', '{$url}')",
            ]
        );
    }

    /**
     * @return bool
     */
    protected function isAllowDeleteShipment()
    {
        $shipmentId = $this->getShipmentId();
        $shipment = $this->shipment->load($shipmentId);

        return $shipment->isAllowDeleteShipment();
    }

    /**
     * @return string
     */
    protected function getDeleteUrl()
    {
        return $this->getUrl('iwdordermanager/shipment/delete', ['shipment_id' => $this->getShipmentId()]);
    }

    /**
     * @return integer
     */
    protected function getShipmentId()
    {
        return $this->coreRegistry->registry('current_shipment')->getId();
    }
}
