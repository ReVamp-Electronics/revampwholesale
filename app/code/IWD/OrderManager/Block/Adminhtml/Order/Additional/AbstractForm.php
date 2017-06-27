<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Additional;

use Magento\Backend\Block\Template;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Block\Template\Context;
use IWD\OrderManager\Model\Quote\Quote;
use IWD\OrderManager\Model\Order\Order;

/**
 * Class AbstractForm
 * @package IWD\OrderManager\Block\Adminhtml\Order\Additional
 */
class AbstractForm extends Template
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }
}
