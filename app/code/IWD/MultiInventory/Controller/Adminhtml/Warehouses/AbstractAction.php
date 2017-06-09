<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses;

use IWD\MultiInventory\Api\SourceRepositoryInterface;
use IWD\MultiInventory\Api\Data\SourceInterface;
use IWD\MultiInventory\Api\SourceAddressRepositoryInterface;
use IWD\MultiInventory\Api\Data\SourceAddressInterface;

/**
 * Class AbstractAction
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses
 */
abstract class AbstractAction extends \Magento\Backend\App\AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_MultiInventory::iwdmultiinventory_warehouse';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var SourceAddressRepositoryInterface
     */
    private $sourceAddressRepository;

    /**
     * @var SourceAddressInterface
     */
    private $sourceAddress;

    /**
     * AbstractAction constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \IWD\MultiInventory\Model\Warehouses\Source $source
     * @param SourceRepositoryInterface $sourceRepositoryInterface
     * @param SourceInterface $sourceInterface
     * @param SourceAddressRepositoryInterface $sourceAddressRepositoryInterface
     * @param SourceAddressInterface $sourceAddressInterface
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \IWD\MultiInventory\Model\Warehouses\Source $source,
        SourceRepositoryInterface $sourceRepositoryInterface,
        SourceInterface $sourceInterface,
        SourceAddressRepositoryInterface $sourceAddressRepositoryInterface,
        SourceAddressInterface $sourceAddressInterface
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->sourceRepository = $sourceRepositoryInterface;
        $this->source = $sourceInterface;
        $this->sourceAddressRepository = $sourceAddressRepositoryInterface;
        $this->sourceAddress = $sourceAddressInterface;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function initAction()
    {
        return $this->resultPageFactory->create();
    }

    /**
     * @return SourceRepositoryInterface
     */
    public function getSourceRepository()
    {
        return $this->sourceRepository;
    }

    /**
     * @return SourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return SourceAddressRepositoryInterface
     */
    public function getSourceAddressRepository()
    {
        return $this->sourceAddressRepository;
    }

    /**
     * @return SourceAddressInterface
     */
    public function getSourceAddress()
    {
        return $this->sourceAddress;
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getCoreRegistry()
    {
        return $this->coreRegistry;
    }
}
