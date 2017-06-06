<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Order
 * @package Aheadworks\Helpdesk\Ui\Component\Listing\Columns
 */
class Order extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Order repository model (by default)
     *
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare data source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource['data']['items'] as &$item) {
            $item['order_increment_id'] = $this->prepareContent($item['order_id']);
        }
        return $dataSource;
    }

    /**
     * Prepare content
     *
     * @param $orderId
     * @return string
     */
    protected function prepareContent($orderId)
    {
        try {
            $orderModel = $this->orderRepository->get($orderId);
            $orderIncrementId = '#' . $orderModel->getIncrementId();
            $url = $this->context->getUrl('sales/order/view', ['order_id' => $orderModel->getEntityId()]);
            $orderLabel =  '<a href="' . $url . '">' . $orderIncrementId . '</a>';
        } catch (\Exception $e) {
            $orderLabel = __('Unassigned')->render();
        }

        return $orderLabel;
    }
}