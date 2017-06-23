<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Component\Form;

class AttributeMerger extends \Magento\Checkout\Block\Checkout\AttributeMerger
{
    /**
     * Map form element
     *
     * @var array
     */
    protected $formElementMap = [
        'input'       => 'Amasty_CustomerAttributes/js/form/element/abstract',
        'radios'      => 'Amasty_CustomerAttributes/js/form/element/abstract',
        'select'      => 'Amasty_CustomerAttributes/js/form/element/select',
        'date'        => 'Amasty_CustomerAttributes/js/form/element/date',
        'datetime'    => 'Amasty_CustomerAttributes/js/form/element/date',
        'textarea'    => 'Amasty_CustomerAttributes/js/form/element/textarea',
        'checkboxes'  => 'Amasty_CustomerAttributes/js/form/element/checkboxes',
        'multiselectimg'  => 'Amasty_CustomerAttributes/js/form/element/checkboxes',
        'selectimg'  => 'Amasty_CustomerAttributes/js/form/element/abstract',
        'multiselect' => 'Amasty_CustomerAttributes/js/form/element/multiselect',
    ];

    /**
     * Merge additional address fields for given provider
     *
     * @param array $elements
     * @param string $providerName name of the storage container used by UI component
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array
     */
    public function merge($elements, $providerName, $dataScopePrefix, array $fields = [])
    {
        foreach ($elements as $attributeCode => $attributeConfig) {
            $additionalConfig = isset($attributeConfig['config']) ? $attributeConfig : [];
            if (!$this->isFieldVisible($attributeCode, $attributeConfig, $additionalConfig)) {
                continue;
            }
            $config = $this->getFieldConfig(
                $attributeCode,
                $attributeConfig,
                $additionalConfig,
                $providerName,
                $dataScopePrefix
            );
            if ($attributeConfig['config']['relations']) {
                $config['config']['relations'] = $attributeConfig['config']['relations'];
            }
            $fields[$attributeCode] = $config;
        }
        return $fields;
    }
}
