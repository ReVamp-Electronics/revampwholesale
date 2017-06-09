<?php

namespace IWD\MultiInventory\Controller\Adminhtml\Warehouses\Product;

/**
 * Class Grid
 * @package IWD\MultiInventory\Controller\Adminhtml\Warehouses\Product
 */
class Grid extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_MultiInventory::iwdmultiinventory_warehouse';

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $resultLayoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
