<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentInterface;

class InlineEditUpdater
{

    protected $editorTypes = array(
        'textarea' => 'textarea',
        'text' => 'text',
        'weight' => 'text',
        'price' => 'text',
        'date' => 'date',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'multiselect'
    );

    protected $validationRules = array(
        'weight' => 'validate-zero-or-greater',
        'price' => 'validate-zero-or-greater'
    );

    /**
     * @param ValidationRules $validationRules
     */
    public function __construct(
        ValidationRules $validationRules
    ) {
//        $this->validationRules = $validationRules;
    }

    /**
     * Add editor config
     *
     * @param UiComponentInterface $column
     * @param string $frontendInput
     * @param array $validationRules
     * @param bool|false $isRequired
     * @return UiComponentInterface
     */
    public function applyEditing(
        UiComponentInterface $column,
        $frontendInput,
        $frontendClass,
        $isRequired = false
    ) {
        if (array_key_exists($frontendInput, $this->editorTypes)) {
            $config = $column->getConfiguration();
            $editorType = $this->editorTypes[$frontendInput];

            if (!(isset($config['editor']) && isset($config['editor']['editorType']))) {
                $config['editor'] = [
                    'editorType' => $editorType
                ];
            }

            $validationRules = isset($this->validationRules[$frontendInput]) ? $this->validationRules[$frontendInput] : array();

            if (!empty($config['editor']['validation'])) {
                $validationRules = array_merge($config['editor']['validation'], $validationRules);
            }

            $config['editor']['validation'] = $validationRules;

            $column->setData('config', $config);
        }
        return $column;
    }
}
