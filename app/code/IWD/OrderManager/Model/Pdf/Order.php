<?php

namespace IWD\OrderManager\Model\Pdf;

use Magento\Sales\Model\Order\Pdf\Invoice;

/**
 * Class Order
 * @package IWD\OrderManager\Model\Pdf
 */
class Order extends Invoice
{
    /**
     * Return PDF document
     *
     * @param  \Magento\Sales\Model\Order[] $orders
     * @return \Zend_Pdf
     */
    public function getPdf($orders = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('order');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);

        foreach ($orders as $order) {
            if ($order->getStoreId()) {
                $this->_localeResolver->emulate($order->getStoreId());
                $this->_storeManager->setCurrentStore($order->getStoreId());
            }
            $page = $this->newPage();
            $this->_setFontBold($page, 10);
            $order->setOrder($order);
            /* Add image */
            $this->insertLogo($page, $order->getStore());
            /* Add address */
            $this->insertAddress($page, $order->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($order->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                /* Keep it compatible with the invoice */
                $item->setQty($item->getQtyOrdered());
                $item->setOrderItem($item);

                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $order);
            if ($order->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getTotalsList()
    {
        $totals = $this->_pdfConfig->getTotals();

        $totals['total_paid'] = [
            "@" => ["title"],
            "title" => "Total Paid",
            "source_field" => "total_paid",
            "font_size" => "7",
            "display_zero" => "0",
            "sort_order" => "1000"
        ];
        $totals['total_refunded'] = [
            "@" => ["title"],
            "title" => "Total Refunded",
            "source_field" => "total_refunded",
            "font_size" => "7",
            "display_zero" => "0",
            "sort_order" => "1100"
        ];
        $totals['total_due'] = [
            "@" => ["title"],
            "title" => "Total Due",
            "source_field" => "total_due",
            "font_size" => "7",
            "display_zero" => "0",
            "sort_order" => "1200"
        ];

        usort($totals, [$this, '_sortTotalsList']);
        $totalModels = [];
        foreach ($totals as $totalInfo) {
            $class = empty($totalInfo['model']) ? null : $totalInfo['model'];
            $totalModel = $this->_pdfTotalFactory->create($class);
            $totalModel->setData($totalInfo);
            $totalModels[] = $totalModel;
        }

        return $totalModels;
    }
}
