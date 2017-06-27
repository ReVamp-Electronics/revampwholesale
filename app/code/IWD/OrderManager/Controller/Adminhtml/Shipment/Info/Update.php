<?php

namespace IWD\OrderManager\Controller\Adminhtml\Shipment\Info;

use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Helper\Data;
use IWD\OrderManager\Model\Shipment\Log\Logger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Shipment\Info
 */
class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|null
     */
    protected $shipment = null;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string $actionType
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        ShipmentRepositoryInterface $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $actionType = self::ACTION_UPDATE
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $helper,
            $actionType
        );
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @return string|string[]
     */
    protected function getResultHtml()
    {
        $this->updateShipment();
        return $this->prepareResponse();
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->loadShipment()->getOrderId();
    }

    /**
     * @return void
     */
    public function addLogs()
    {
        Logger::getInstance()->saveLogs($this->shipment);
    }

    /**
     * @return array
     */
    protected function prepareResponse()
    {
        return ['result' => 'reload'];
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function updateShipment()
    {
        $this->loadShipment();

        Logger::getInstance()->addMessageForLevel('shipment_info', 'Shipment information was changed');

        $this->updateIncrementId();
        $this->updateCreatedAt();

        $this->shipmentRepository->save($this->shipment);
    }

    /**
     * @return void
     */
    public function updateIncrementId()
    {
        $incrementId = $this->getIncrementId();
        Logger::getInstance()->addChange(
            'Increment Id',
            $this->shipment->getIncrementId(),
            $incrementId,
            'shipment_info'
        );
        $this->shipment->setIncrementId($incrementId);
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getIncrementId()
    {
        $incrementId = $this->getShipmentData('increment_id');
        $incrementId = trim($incrementId);

        if ($this->shipment->getIncrementId() == $incrementId) {
            return $incrementId;
        }

        if (empty($incrementId)) {
            throw new LocalizedException(__("Shipment number is empty"));
        }

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        $collection = $this->shipmentRepository->getList($searchCriteria);

        if ($collection->getTotalCount() > 0) {
            throw new LocalizedException(__("Shipment number #$incrementId is already exists"));
        }

        return $incrementId;
    }

    /**
     * @return void
     */
    public function updateCreatedAt()
    {
        $createdAt = $this->getShipmentData('created_at');
        Logger::getInstance()->addChange('Created At', $this->shipment->getCreatedAt(), $createdAt, 'shipment_info');
        $this->shipment->setCreatedAt($createdAt);
    }

    /**
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     * @throws \Exception
     */
    protected function loadShipment()
    {
        if ($this->shipment == null) {
            $shipmentId = $this->getShipmentId();
            $this->shipment = $this->shipmentRepository->get($shipmentId);
            if (!$this->shipment->getEntityId()) {
                throw new LocalizedException(__('Can not load shipment with id ' . $shipmentId));
            }
        }

        return $this->shipment;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getShipmentId()
    {
        $id = $this->getRequest()->getParam('shipment_id', null);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param shipment_id'));
        }

        return $id;
    }

    /**
     * @param bool|string $id
     * @return array|string
     * @throws \Exception
     */
    protected function getShipmentData($id=false)
    {
        $data = $this->getRequest()->getParam('shipment_info', []);

        if (empty($id)) {
            return $data;
        } elseif (isset($data[$id]) && !empty($data[$id])) {
            return $data[$id];
        }

        throw new LocalizedException(__('Empty param shipment_info[' . $id . ']'));
    }
}
