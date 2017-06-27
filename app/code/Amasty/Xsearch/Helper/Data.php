<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

namespace Amasty\Xsearch\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_TEMPLATE_CATEGORY_POSITION = 'amasty_xsearch/category/position';
    const XML_PATH_TEMPLATE_PRODUCT_POSITION = 'amasty_xsearch/product/position';
    const XML_PATH_TEMPLATE_PAGE_POSITION = 'amasty_xsearch/page/position';
    const XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION = 'amasty_xsearch/popular_searches/position';
    const XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION = 'amasty_xsearch/recent_searches/position';

    const XML_PATH_TEMPLATE_CATEGORY_ENABLED = 'amasty_xsearch/category/enabled';
    const XML_PATH_TEMPLATE_PRODUCT_ENABLED = 'amasty_xsearch/product/enabled';
    const XML_PATH_TEMPLATE_PAGE_ENABLED = 'amasty_xsearch/page/enabled';
    const XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED = 'amasty_xsearch/popular_searches/enabled';
    const XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED = 'amasty_xsearch/recent_searches/enabled';

    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $configAttribute;

    protected $collection;

    public function __construct(
        \Magento\Catalog\Model\Config $configAttribute,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->configAttribute = $configAttribute;
        $this->collection = $collection;
    }

    public function highlight($text, $query)
    {
        preg_match_all('~\w+~', $query, $m);

        if (!$m) {
            return $text;
        }

        $re = '/(' . implode('|', $m[0]) . ')/iu';

        return preg_replace($re, '<span class="amasty-xsearch-highlight">$0</span>', $text);
    }

    protected function _pushItem($position, $block, &$html)
    {
        $position = $this->_scopeConfig->getValue($position);
        while (isset($html[$position])) {
            $position++;
        }
        $html[$position] = $block->toHtml();
    }

    public function getBlocksHtml(\Magento\Framework\View\Layout $layout)
    {
        $html = [];

        if ($this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_CATEGORY_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_CATEGORY_POSITION,
                $layout->createBlock('Amasty\Xsearch\Block\Category', 'amasty.xsearch.category'),
                $html
            );
        }

        if ($this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_PRODUCT_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_PRODUCT_POSITION,
                $layout->createBlock('Amasty\Xsearch\Block\Product', 'amasty.xsearch.product'),
                $html
            );
        }

        if ($this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_PAGE_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_PAGE_POSITION,
                $layout->createBlock('Amasty\Xsearch\Block\Page', 'amasty.xsearch.page'),
                $html
            );
        }

        if ($this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION,
                $layout->createBlock('Amasty\Xsearch\Block\Search\Popular', 'amasty.xsearch.search.popular'),
                $html
            );
        }

        if ($this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION,
                $layout->createBlock('Amasty\Xsearch\Block\Search\Recent', 'amasty.xsearch.search.recent'),
                $html
            );
        }

        ksort($html);

        return implode('', $html);
    }

    /**
     * @param string $requiredData
     * @return array
     */
    public function getProductAttributes($requiredData = '')
    {
        if ($requiredData == 'is_searchable') {
            $attributeNames = null;
            foreach ($this->collection->addIsSearchableFilter()->getItems() as $attribute) {
                $attributeNames[] = $attribute->getAttributeCode();
            }
            return $attributeNames;
        } else {
            return $this->collection->getItems();
        }
    }
}