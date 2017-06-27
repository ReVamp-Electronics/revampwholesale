<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */


namespace Amasty\CustomerAttributes\Block\Widget\Form\Renderer;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Fieldset extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
{
    protected $_template = 'Amasty_CustomerAttributes::widget/form/renderer/fieldset.phtml';

    public function getRelationJson()
    {
        $depends = $this->getElement()->getData('depends');
        if (!$depends) {
            return '';
        }
        foreach ($depends as &$relation) {
            $relation['parent_attribute_element_uid'] = $this->getJsId(
                'form-field',
                $relation['parent_attribute_code']
            );
            $relation['depend_attribute_element_uid'] = $this->getJsId(
                'form-field',
                $relation['depend_attribute_code']
            );
        }
        $this->getElement()->setData('depends', $depends);

        return $this->getElement()->convertToJson(['depends']);
    }
}
