<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\View\Tab;

use Magento\Framework\View\Element\Text\ListText;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Log
 * @package IWD\OrderManager\Block\Adminhtml\Order\View\Tab
 */
class Log extends ListText implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * Collection factory
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Log');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Log');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
