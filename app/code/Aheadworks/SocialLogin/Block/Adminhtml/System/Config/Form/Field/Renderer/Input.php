<?php
namespace Aheadworks\SocialLogin\Block\Adminhtml\System\Config\Form\Field\Renderer;

/**
 * HTML input element block
 */
class Input extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * Get additional attributes
     *
     * @return string
     */
    protected function getAdditionalAttributes()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $column = $this->getColumn();

        return '<input type="text" id="' . $this->getInputId() .
            '"' .
            $this->getAdditionalAttributes() .
            ' name="' .
            $this->getInputName() .
            '" value="<%- ' .
            $this->getColumnName() .
            ' %>" ' .
            ($column['size'] ? 'size="' .
                $column['size'] .
                '"' : '') .
            ' class="' .
            (isset(
                $column['class']
            ) ? $column['class'] : 'input-text') . '"' . (isset(
                $column['style']
            ) ? ' style="' . $column['style'] . '"' : '') . '/>';
    }
}
