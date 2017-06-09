<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */

namespace Amasty\Xsearch\Block;

class Jsinit extends \Magento\Framework\View\Element\Template
{
    const XML_PATH_TEMPLATE_WIDTH = 'amasty_xsearch/general/width';
    const XML_PATH_TEMPLATE_MIN_CHARS = 'amasty_xsearch/general/min_chars';

    const XML_PATH_RECENT_SEARCHES_FIRST_CLICK = 'amasty_xsearch/recent_searches/first_click';

    const XML_PATH_LAYOUT_ENABLED = 'amasty_xsearch/layout/enabled';
    const XML_PATH_LAYOUT_BORDER = 'amasty_xsearch/layout/border';
    const XML_PATH_LAYOUT_HOVER = 'amasty_xsearch/layout/hover';
    const XML_PATH_LAYOUT_HIGHLIGHT = 'amasty_xsearch/layout/highlight';
    const XML_PATH_LAYOUT_BACKGROUND = 'amasty_xsearch/layout/background';
    const XML_PATH_LAYOUT_TEXT = 'amasty_xsearch/layout/text';
    const XML_PATH_LAYOUT_HOVER_TEXT = 'amasty_xsearch/layout/hover_text';

    protected $_recentSearch;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlHelper = $urlHelper;
    }

    public function getWidth()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_WIDTH);
    }

   public function getMinChars()
   {
       return $this->_scopeConfig->getValue(self::XML_PATH_TEMPLATE_MIN_CHARS);
   }

    public function getLayoutEnabled()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LAYOUT_ENABLED);
    }

    public function getLayoutBorder()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LAYOUT_BORDER);
    }

    public function getLayoutHover()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LAYOUT_HOVER);
    }

    public function getLayoutHighlight()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LAYOUT_HIGHLIGHT);
    }

    public function getLayoutBackground()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LAYOUT_BACKGROUND);
    }

    public function getLayoutText()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LAYOUT_TEXT);
    }

    public function getLayoutHoverText()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_LAYOUT_HOVER_TEXT);
    }

    protected function _getRecentSearch()
    {
        if (!$this->_recentSearch) {
            $this->_recentSearch = $this->_layout->createBlock('Amasty\Xsearch\Block\Search\Recent', 'amasty.xsearch.search.recent');
            $this->_recentSearch->toHtml();
        }

        return $this->_recentSearch;
    }

    public function getLoadPreload()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_RECENT_SEARCHES_FIRST_CLICK) && $this->_getRecentSearch()->getLoadedSearchCollection()->getSize() > 0;
    }

    public function getPreload()
    {
        return $this->_getRecentSearch()->toHtml();
    }

    public function getCurrentUrlEncoded()
    {
        return $this->urlHelper->getEncodedUrl();
    }

}
