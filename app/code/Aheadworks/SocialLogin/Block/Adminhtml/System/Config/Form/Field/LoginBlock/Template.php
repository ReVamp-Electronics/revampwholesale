<?php
namespace Aheadworks\SocialLogin\Block\Adminhtml\System\Config\Form\Field\LoginBlock;

/**
 * Class Template
 */
class Template extends \Aheadworks\SocialLogin\Block\Adminhtml\System\Config\Form\Field\Renderer\Select
{
    /**
     * @var \Aheadworks\SocialLogin\Model\Config\Source\LoginBlock\Template
     */
    protected $templateSource;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Aheadworks\SocialLogin\Model\Config\Source\LoginBlock\Template $templateSource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Aheadworks\SocialLogin\Model\Config\Source\LoginBlock\Template $templateSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->templateSource = $templateSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->templateSource->toOptionArray();
    }
}
