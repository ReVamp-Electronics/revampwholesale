<?php
namespace Aheadworks\SocialLogin\Block\Customer\Login;

use Aheadworks\SocialLogin\Block\Customer\Login;

/**
 * Class Configurable Login
 */
class Configurable extends Login
{
    /**
     * Is block visible
     *
     * @var bool
     */
    protected $isVisible = true;

    /**
     * @var \Aheadworks\SocialLogin\Model\LoginBlock\Settings
     */
    protected $blockSettings;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Aheadworks\SocialLogin\Model\Config\General $moduleConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Aheadworks\SocialLogin\Model\LoginBlock\Settings $blockSettings
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Aheadworks\SocialLogin\Model\Config\General $moduleConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Aheadworks\SocialLogin\Model\ProviderManagement $providerManagement,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Aheadworks\SocialLogin\Model\LoginBlock\Settings $blockSettings,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $moduleConfig,
            $httpContext,
            $providerManagement,
            $postDataHelper,
            $data
        );
        $this->blockSettings = $blockSettings;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockSettings();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare block settings
     *
     * @return $this
     */
    protected function prepareBlockSettings()
    {
        $this->isVisible = $this->blockSettings->isGroupVisible($this->getConfigurableGroup());

        $template = $this->blockSettings->getGroupTemplate($this->getConfigurableGroup());
        $this->setTemplate($template->getPath());
        $this->addData($template->getAdditionalData());

        return $this;
    }

    /**
     * Get configurable group
     *
     * @return string
     */
    public function getConfigurableGroup()
    {
        return $this->getData('configurable_group');
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        return $this->isVisible ? parent::_toHtml() : '';
    }
}
