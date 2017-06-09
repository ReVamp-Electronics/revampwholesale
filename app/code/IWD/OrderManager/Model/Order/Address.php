<?php

namespace IWD\OrderManager\Model\Order;

use Magento\Sales\Model\AbstractModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use IWD\OrderManager\Model\Log\Logger;

/**
 * Class Address
 * @package IWD\OrderManager\Model\Order
 */
class Address extends AbstractModel
{
    /**
     * @var $address OrderAddressInterface|\Magento\Sales\Model\Order\Address
     */
    private $address;

    /**
     * @var $oldAddress OrderAddressInterface|\Magento\Sales\Model\Order\Address
     */
    private $oldAddress;

    /**
     * @var \Magento\Customer\Model\Address
     */
    private $customerAddress;

    /**
     * @var OrderAddressRepositoryInterface
     */
    private $orderAddressCollection;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    private $addressRenderer;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private $regionFactory;

    /**
     * @var string[]
     */
    private $addressMap = [
        'fax'        => 'Fax',
        'region'     => 'State/Province',
        'postcode'   => 'Zip/Postal Code',
        'lastname'   => 'Last Name',
        'street'     => 'Street Address',
        'city'       => 'City',
        'email'      => 'Email',
        'telephone'  => 'Phone Number',
        'country_id' => 'Country',
        'firstname'  => 'First Name',
        'prefix'     => 'Prefix',
        'middlename' => 'Middle Name/Initial',
        'suffix'     => 'Suffix',
        'company'    => 'Company',
        'vat_id'     => 'VAT Number',
    ];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param OrderAddressRepositoryInterface $orderAddressCollection
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Model\Address $customerAddress
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        OrderAddressRepositoryInterface $orderAddressCollection,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Customer\Model\Address $customerAddress,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );

        $this->orderAddressCollection = $orderAddressCollection;
        $this->addressRenderer = $addressRenderer;
        $this->regionFactory = $regionFactory;
        $this->customerAddress = $customerAddress;
    }

    /**
     * @param string[] $addressData
     * @return void
     */
    public function updateAddress($addressData)
    {
        $addressData = $this->prepareRegion($addressData);
        $this->oldAddress = $this->address->getData();

        $this->address->addData($addressData);
        $this->address->save();

        $this->addInfoToLog();

        $this->_eventManager->dispatch(
            'admin_sales_order_address_update',
            ['order_id' => $this->address->getParentId()]
        );
    }

    /**
     * @param string[] $addressData
     * @return void
     */
    public function updateCustomerAddress($addressData)
    {
        $customerAddressId = $this->address->getCustomerAddressId();

        $this->customerAddress->load($customerAddressId);
        if ($this->customerAddress->getId()) {
            $this->customerAddress->addData($addressData);
            $this->customerAddress->save();
        }
    }

    /**
     * @param string[] $addressData
     * @return string[]
     */
    private function prepareRegion($addressData)
    {
        if (isset($addressData['region_id']) && !empty($addressData['region_id'])
            && (!isset($addressData['region']) || empty($addressData['region']))
        ) {
            $addressData['region'] = $this->regionFactory->create()
                ->load($addressData['region_id'])
                ->getName();
        }

        return $addressData;
    }

    /**
     * @return null|string
     */
    public function getFormattedAddressString()
    {
        return $this->addressRenderer->format($this->address, 'html');
    }

    /**
     * @param int $addressId
     * @return OrderAddressInterface|\Magento\Sales\Model\Order\Address
     * @throws \Exception
     */
    public function loadAddress($addressId)
    {
        /** @var $address OrderAddressInterface|\Magento\Sales\Model\Order\Address */
        $address = $this->orderAddressCollection->get($addressId);
        if (!$address || !$address->getId()) {
            throw new LocalizedException(__('Can not update order address data'));
        }

        return $this->address = $address;
    }

    /**
     * @return void
     */
    private function addInfoToLog()
    {
        $type = $this->address->getAddressType();
        $level = $type . '_address';
        $addressType = __(ucfirst($type));

        $message = __("%1 address(es) has been successfully updated.", $addressType);
        $logger = Logger::getInstance();
        $logger->addMessageForLevel($level, $message);

        foreach ($this->addressMap as $index => $title) {
            $old = $this->oldAddress[$index];
            $new = $this->address->getData($index);
            $logger->addChange($title, $old, $new, $level);
        }
    }
}
