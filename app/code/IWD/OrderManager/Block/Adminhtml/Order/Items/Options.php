<?php

namespace IWD\OrderManager\Block\Adminhtml\Order\Items;

use IWD\OrderManager\Model\Quote\Item;
use Magento\Backend\Block\Template;
use Magento\Downloadable\Model\Link;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Options
 * @package IWD\OrderManager\Block\Adminhtml\Order\Items
 */
class Options extends Template
{
    /**
     * @var Item|null
     */
    private $orderItem = null;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    private $optionFactory;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    private $purchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    private $downloadableLink;

    /**
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\Product\OptionFactory $optionFactory
     * @param Link\PurchasedFactory $purchasedFactory
     * @param Link $downloadableLink
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        \Magento\Downloadable\Model\Link\PurchasedFactory $purchasedFactory,
        \Magento\Downloadable\Model\Link $downloadableLink,
        array $data = []
    ) {
        $this->optionFactory = $optionFactory;
        $this->purchasedFactory = $purchasedFactory;
        $this->downloadableLink = $downloadableLink;

        parent::__construct($context, $data);
    }

    /**
     * @param Item $orderItem
     * @return $this
     */
    public function setOrderItem($orderItem)
    {
        $this->orderItem = $orderItem;
        return $this;
    }

    /**
     * @return Item
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return string[]
     */
    public function getOrderOptions()
    {
        $result = [];
        $options = $this->getOrderItem()->getProductOptions();

        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['links'])) {
                $result = array_merge($result, $this->getLinksOptions($options['links']));
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }

        return $result;
    }

    /**
     * @param string[] $options
     * @return string[][]
     */
    public function getLinksOptions($options)
    {
        $links = [];

        foreach ($options as $linkId) {
            /**
             * @var $link \Magento\Downloadable\Model\Link
             */
            $link = $this->downloadableLink->getCollection()
                ->addTitleToResult()
                ->addFieldToFilter('main_table.link_id', $linkId)
                ->getFirstItem();

            $links[] = $link->getDefaultTitle();
        }

        return [[
            'label' => $this->getLinksTitle(),
            'value' => implode(', ', $links)
        ]];
    }

    /**
     * @return string
     */
    public function getLinksTitle()
    {
        $purchasedLinks = $this->purchasedFactory->create()->load(
            $this->getOrderItem()->getId(),
            'order_item_id'
        );

        $linkSectionTitle = $purchasedLinks->getLinkSectionTitle();

        return $linkSectionTitle ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string[] $optionInfo
     * @return string
     */
    public function getCustomizedOptionValue($optionInfo)
    {
        // render customized option view
        $_default = $optionInfo['value'];
        if (isset($optionInfo['option_type'])) {
            try {
                return $this->optionFactory->create()
                    ->groupFactory($optionInfo['option_type'])
                    ->getCustomizedView($optionInfo);
            } catch (\Exception $e) {
                return $_default;
            }
        }

        return $_default;
    }

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncateString($value, $length = 80, $etc = '...', &$remainder = '', $breakWords = true)
    {
        return $this->filterManager->truncate(
            $value,
            ['length' => $length, 'etc' => $etc, 'remainder' => $remainder, 'breakWords' => $breakWords]
        );
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption($value)
    {
        $remainder = '';
        $value = $this->truncateString($value, 55, '', $remainder);
        $result = ['value' => nl2br($value), 'remainder' => nl2br($remainder)];

        return $result;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        $prefix = $this->getEditedItemType() == 'quote' ? Item::PREFIX_ID : '';
        return $prefix . $this->getOrderItem()->getItemId();
    }

    /**
     * @return string
     */
    public function getEditedItemType()
    {
        if ($this->hasItemType()) {
            return $this->getItemType();
        }
        return 'order';
    }
}
