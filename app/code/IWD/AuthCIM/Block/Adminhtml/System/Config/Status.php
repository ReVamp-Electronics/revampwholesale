<?php

namespace IWD\AuthCIM\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use IWD\AuthCIM\Model\Method;

/**
 * Class Status
 * @package IWD\AuthCIM\Block\Adminhtml\System\Config
 */
class Status extends Field
{
    /**
     * @var Method
     */
    private $method;

    /**
     * @param Context $context
     * @param Method $method
     * @param array $data
     */
    public function __construct(
        Context $context,
        Method $method,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->method = $method;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->_scopeConfig->isSetFlag('advanced/modules_disable_output/IWD_AuthCIM')) {
            $message = '<b style="color:#D40707">' . __('Module Output Is Disabled') . '</b>';
        } elseif (!$this->method->isEnabled()) {
            $message = '<b style="color:#D40707">' . __('Module Is Disabled') . '</b>';
        } else {
            $message = ($this->method->checkApiCredentials() !== true)
                ? '<b style="color:#D40707">' . __($this->method->getErrorMessage()) . '</b>'
                : '<b style="color:#059147">' . __('Module Is Enabled') . '</b>';
        }

        return "<span style='margin-bottom:-8px; display:block;'>" . $message . "</span>";
    }
}
