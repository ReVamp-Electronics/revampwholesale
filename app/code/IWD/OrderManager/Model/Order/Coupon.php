<?php

namespace IWD\OrderManager\Model\Order;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Coupon
 * @package IWD\OrderManager\Model\Order
 */
class Coupon extends Order
{
    /**
     * @param $couponCode
     * @return bool
     */
    public function updateCoupon($couponCode)
    {
        $this->syncQuote();

        if ($this->applyCoupon($couponCode)) {
            $params = $this->prepareItemsParams();
            $this->editItems($params);
            $this->setCouponCode($couponCode)
                ->setDiscountDescription($couponCode)
                ->save();
            return true;
        }

        return false;
    }

    /**
     * @param $couponCode
     * @return bool
     * @throws \Exception
     */
    private function applyCoupon($couponCode)
    {
        $codeLength = strlen($couponCode);
        if ($codeLength) {
            if ($codeLength > \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH) {
                throw new LocalizedException(__('The coupon code is not valid.'));
            }

            $coupon = $this->couponFactory->create();
            $coupon->load($couponCode, 'code');
            if (!$coupon->getId()) {
                throw new LocalizedException(__('The coupon code is not valid.'));
            }
        }

        $itemsCount = $this->getQuote()->getItemsCount();
        if ($itemsCount) {
            $this->getQuote()->setCouponCode($couponCode);
            $this->collectQuoteTotals();
        } else {
            throw new LocalizedException(__('We cannot apply the coupon code.'));
        }

        return true;
    }

    /**
     * @return array
     */
    protected function prepareItemsParams()
    {
        $result = [];
        $params = [
            'price',
            'price_incl_tax',
            'tax_amount',
            'tax_percent',
            'discount_amount',
            'discount_percent',
            'row_total',
            'weee_tax_applied_row_amount',
            'discount_tax_compensation_amount',
            'product_id'
        ];

        $quoteItems = $this->getQuote()->getAllItems();
        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $quoteItemId = $quoteItem->getItemId();
            foreach ($params as $param) {
                $result[$quoteItemId][$param] = $quoteItem->getData($param);
            }

            $result[$quoteItemId]['item_id'] = $quoteItemId;
            $result[$quoteItemId]['item_type'] = 'quote';
            $result[$quoteItemId]['fact_qty'] = $quoteItem->getQty();
            $result[$quoteItemId]['subtotal'] = $quoteItem->getRowTotal();
            $result[$quoteItemId]['subtotal_incl_tax'] = $quoteItem->getRowTotalInclTax();
        }

        $orderItems = $this->getAllItems();
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            $orderItemId = $orderItem->getItemId();
            $quoteItemId = $orderItem->getQuoteItemId();
            if ($quoteItemId != $orderItemId) {
                $result[$orderItemId] = $result[$quoteItemId];
                $result[$orderItemId]['item_id'] = $orderItemId;
                $result[$orderItemId]['item_type'] = 'order';
                $result[$orderItemId]['description'] = $orderItem->getDescription();
                unset($result[$quoteItemId]);
            }
        }

        return [
            'order_id' => $this->getEntityId(),
            'item' => $result
        ];
    }
}
