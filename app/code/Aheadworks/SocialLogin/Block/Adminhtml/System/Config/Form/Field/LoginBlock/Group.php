<?php
namespace Aheadworks\SocialLogin\Block\Adminhtml\System\Config\Form\Field\LoginBlock;

/**
 * Class Group
 */
class Group extends \Aheadworks\SocialLogin\Block\Adminhtml\System\Config\Form\Field\Renderer\Input
{
    /**
     * {@inheritdoc}
     */
    protected function getAdditionalAttributes()
    {
        return ' <%- !is_group_editable ? \\\'readonly\\\' : \\\'\\\' %>';
    }
}
