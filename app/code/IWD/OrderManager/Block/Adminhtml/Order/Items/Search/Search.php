<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items\Search;

/**
 * Class Search
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items\Search
 */
class Search extends \Magento\Sales\Block\Adminhtml\Order\Create\Search
{
    /**
     * Get buttons html
     * @return string
     */
    public function getButtonsHtml()
    {
        $addButtonHtml = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'label' => __('Add Selected Product(s) to Order'),
                    'class' => 'action-add action-secondary',
                    'id'    => 'iwd-om-update-add-products'
                ]
            )->toHtml();

        $cancelButtonHtml = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'label' => __('Cancel'),
                    'class' => 'action-cancel action-secondary',
                    'id'    => 'iwd-om-cancel-add-products'
                ]
            )->toHtml();

        return $cancelButtonHtml . $addButtonHtml;
    }
}
