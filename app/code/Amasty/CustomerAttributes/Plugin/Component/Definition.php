<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Plugin\Component;

class Definition
{
    protected $formElementMap = [
        'statictext'     => 'Amasty_CustomerAttributes/js/form/element/abstract',
        'selectimg'      => 'Amasty_CustomerAttributes/js/form/element/abstract',
        'multiselectimg' => 'Amasty_CustomerAttributes/js/form/element/checkboxes',
        'file'           => 'Amasty_CustomerAttributes/js/form/element/file'
    ];

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $name
     * @return array|mixed
     */
    public function aroundGetComponentData($subject, \Closure $proceed, $name)
    {
        try {
            /*
             * compatibility with old versions
            if ($name == 'file') {
                throw new \Exception('Amasty Customer Attribute File field');
            }*/
            $result = $proceed($name);
        } catch (\Exception $ex) {

            $class  = 'Amasty\CustomerAttributes\Component\Form\Element\\' . ucfirst($name);
            $result = [
                '@arguments'  => [
                    'data' => [
                        'name'     => 'data',
                        'xsi:type' => 'array',
                        'item'     => [
                            'js_config' => [
                                'name'     => 'js_config',
                                'xsi:type' => 'array',
                                'item'     => [
                                    'component' => [
                                        'name'     => 'component',
                                        'xsi:type' => 'string',
                                        'value'    => $this->formElementMap[$name] // element type 'Magento_Ui/js/form/element/select'
                                    ],
                                    'config'    => [
                                        'name'     => 'config',
                                        'xsi:type' => 'array',
                                        'item'     => [
                                            'template'    => [
                                                'name'     => 'template',
                                                'xsi:type' => 'string',
                                                'value'    => 'ui/form/field'
                                            ],
                                            'elementTmpl' => [
                                                'name'     => 'elementTmpl',
                                                'xsi:type' => 'string',
                                                'value'    => $this->getElementTmpl($name)
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                '@attributes' => [
                    'class' => $class
                ]
            ];
        }

        return $result;
    }

    protected function getElementTmpl($attributeFrontendInput)
    {
        switch ($attributeFrontendInput) {
            case 'radios':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/radios';
                break;
            case 'checkboxes':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/checkboxes';
                break;
            case 'datetime':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/datetime';
                break;
            case 'multiselect':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/multiselect';
                break;
            case 'multiselectimg':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/multiselectimg';
                break;
            case 'selectimg':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/selectimg';
                break;
            case 'statictext':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/statictext';
                break;
            case 'file':
                $elementTmpl = 'Amasty_CustomerAttributes/form/element/media';
                break;
            default:
                $elementTmpl = '';
                break;
        }

        return $elementTmpl;
    }
}
