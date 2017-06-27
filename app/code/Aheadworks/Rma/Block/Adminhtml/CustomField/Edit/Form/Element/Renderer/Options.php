<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Block\Adminhtml\CustomField\Edit\Form\Element\Renderer;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Options
 * @package Aheadworks\Rma\Block\Adminhtml\CustomField\Edit\Form\Element\Renderer
 */
class Options extends \Magento\Backend\Block\Template implements RendererInterface
{
    /**
     * @var null|\Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected $stores = null;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $element;

    /**
     * @var null|array
     */
    protected $optionValues = null;

    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::customfield/edit/form/options.phtml';

    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->element = $element;
        return $this->toHtml();
    }

    /**
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function getStores()
    {
        if ($this->stores === null) {
            $this->stores = $this->_storeManager->getStores(true);
            ksort($this->stores);
        }
        return $this->stores;
    }

    /**
     * @return array
     */
    public function getOptionValues()
    {
        if ($this->optionValues === null) {
            $this->optionValues = [];

            $values = $this->element->getOptionValues('value');
            if (is_array($values)) {
                $sortOrder = $this->element->getOptionValues('order');
                $default = $this->element->getOptionValues('default');
                $enable = $this->element->getOptionValues('enable');

                foreach ($values as $id => $data) {
                    $optionValue = [
                        'id' => $id,
                        'sort_order' => $sortOrder[$id],
                        'checked' => in_array($id, $default) ? 'checked' : '',
                        'enable' => in_array($id, $enable) ? 'checked' : ''
                    ];
                    foreach ($this->getStores() as $store) {
                        $storeId = $store->getId();
                        if (isset($data[$storeId])) {
                            $optionValue['store' . $storeId] = $data[$storeId];
                        }
                    }
                    $this->optionValues[] = $optionValue;
                }
            }
        }
        return $this->optionValues;
    }

    /**
     * @return bool
     */
    public function allowAddOption()
    {
        return $this->element->getAllowAddOption();
    }

    /**
     * @return bool
     */
    public function allowDisableOption()
    {
        return $this->element->getAllowDisableOption();
    }

    /**
     * @return string
     */
    public function getInitJs()
    {
        $options =  \Zend_Json::encode([
            'tableSelector' => '#custom-field-options-table',
            'rowSelector' => '#row-template',
            'addBtnSelector' => '#add_new_option_button',
            'deleteBtnSelector' => '.delete-option',
            'optionValues' => $this->getOptionValues()
        ]);
        return <<<HTML
    <script>
        require(['jquery', 'awRmaCustomFieldOptions'], function($, customFieldOptions){
            $(document).ready(function() {
                customFieldOptions({$options}, $('#manage-options-panel'));
            });
        });
    </script>
HTML;
    }
}
