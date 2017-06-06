<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Sales\Order;

/**
 * Class CreateTicketLink
 * @package Aheadworks\Helpdesk\Block\Sales\Order
 */
class CreateTicketLink extends \Magento\Framework\View\Element\Template
{
    /**
     * Block template
     * @var string
     */
    protected $_template = 'sales/order/create_ticket_link.phtml';

    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get action url
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl(
            'aw_helpdesk/ticket',
            ['order_id' => $this->getOrder()->getId(), '_secure' => $this->getRequest()->isSecure()]
        ) . "#create_ticket_form";
    }

    /**
     * Get current order
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
}
