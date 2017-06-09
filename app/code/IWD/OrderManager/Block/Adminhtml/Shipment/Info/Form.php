<?php

namespace IWD\OrderManager\Block\Adminhtml\Shipment\Info;

use IWD\OrderManager\Block\Adminhtml\Order\AbstractForm;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class Form
 * @package IWD\OrderManager\Block\Adminhtml\Shipment\Info
 */
class Form extends AbstractForm
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentInterface
     */
    private $shipment;

    /**
     * @var int
     */
    private $shipmentId;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * Form constructor.
     * @param Context $context
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShipmentRepositoryInterface $shipmentRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function getShipment()
    {
        if ($this->shipment == null) {
            $id = $this->getShipmentId();
            $this->shipment = $this->shipmentRepository->get($id);
        }

        return $this->shipment;
    }

    /**
     * @param int $shipmentId
     * @return $this
     */
    public function setShipmentId($shipmentId)
    {
        $this->shipmentId = $shipmentId;
        return $this;
    }

    /**
     * @return int
     */
    public function getShipmentId()
    {
        return $this->shipmentId;
    }
}
