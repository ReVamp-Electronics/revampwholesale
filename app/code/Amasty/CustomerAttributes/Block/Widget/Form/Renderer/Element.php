<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Block\Widget\Form\Renderer;

use Amasty\CustomerAttributes\Model\Validation;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Element extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element
{
    /**
     * Initialize block template
     */
    protected $_template = 'Amasty_CustomerAttributes::widget/form/renderer/fieldset/element.phtml';
    /**
     * @var Validation
     */
    private $validation;

    /**
     * Element constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Validation $validation
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Validation $validation,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->validation = $validation;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = parent::render($element);
        $html .= '<script>' .
            $this->validation->getJS()
            . '</script>';
        return $html;
    }
}
