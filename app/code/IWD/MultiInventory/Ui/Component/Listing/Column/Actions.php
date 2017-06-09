<?php

namespace IWD\MultiInventory\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;

class Actions extends Column
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Order $order
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Order $order,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );

        $this->order = $order;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param array $dataSource
     * @return string[]
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['order_id']) && !empty($item['order_id'])) {
                $order = clone $this->order;
                $orderId = $order->load($item['order_id'])->getId();
                if (!empty($orderId)) {
                    $href = $this->urlBuilder->getUrl(
                        'sales/order/view',
                        ['order_id' => $orderId]
                    );
                    $item['actions'] = [
                        'view_order' => [
                            'href' => $href,
                            'label' => __('View Order'),
                            'hidden' => false,
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
