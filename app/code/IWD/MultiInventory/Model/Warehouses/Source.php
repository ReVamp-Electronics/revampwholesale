<?php

namespace IWD\MultiInventory\Model\Warehouses;

use IWD\MultiInventory\Api\Data\SourceInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Source
 * @package IWD\MultiInventory\Model\Warehouses
 */
class Source extends AbstractExtensibleModel implements SourceInterface
{
    /**
     * @var SourceAddress
     */
    private $sourceAddress;

    /**
     * @var SourceAddressRepository
     */
    private $sourceAddressRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param SourceAddress $sourceAddress
     * @param SourceAddressRepository $sourceAddressRepository,
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        SourceAddress $sourceAddress,
        SourceAddressRepository $sourceAddressRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
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

        $this->sourceAddress = $sourceAddress;
        $this->sourceAddressRepository = $sourceAddressRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init('IWD\MultiInventory\Model\ResourceModel\Warehouses\Source');
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->_getData(self::STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteId()
    {
        return $this->_getData(self::WEBSITE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getStockName()
    {
        return $this->_getData(self::STOCK_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId)
    {
        $this->setData(self::STOCK_ID, $stockId);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setWebsiteId($websiteId)
    {
        $this->setData(self::WEBSITE_ID, $websiteId);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function setStockName($stockName)
    {
        $this->setData(self::STOCK_NAME, $stockName);
        return $this;
    }
}
