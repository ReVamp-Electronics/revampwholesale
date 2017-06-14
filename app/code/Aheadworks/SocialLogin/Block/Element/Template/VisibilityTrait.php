<?php
namespace Aheadworks\SocialLogin\Block\Element\Template;

/**
 * Trait VisibilityTrait
 */
trait VisibilityTrait
{
    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        return $this->getModuleConfig()->isModuleEnabled() ? parent::_toHtml() : '';
    }

    /**
     * @return \Aheadworks\SocialLogin\Model\Config\General
     */
    abstract protected function getModuleConfig();
}
