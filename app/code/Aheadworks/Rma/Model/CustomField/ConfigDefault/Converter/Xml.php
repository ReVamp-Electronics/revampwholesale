<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\CustomField\ConfigDefault\Converter;

class Xml implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Converting data to array type
     *
     * @param mixed $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = [];
        if (!$source instanceof \DOMDocument) {
            return $output;
        }

        $customFields = $source->getElementsByTagName('custom_field');
        foreach ($customFields as $customField) {
            $customFieldData = [];
            /** @var $customField \DOMElement */
            foreach ($customField->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                /** @var $customField \DOMElement */
                if (in_array($child->nodeName, ['visible_for_status_ids', 'editable_for_status_ids', 'editable_admin_for_status_ids'])) {
                    $this->collectStatusesData($child, $customFieldData);
                } elseif ($child->nodeName == 'attribute') {
                    $this->collectAttrData($child, $customFieldData);
                } elseif($child->nodeName == 'option') {
                    $this->collectOptionData($child, $customFieldData);
                } else {
                    $customFieldData[$child->nodeName] = $child->nodeValue;
                }
            }
            $output[] = $customFieldData;
        }
        return $output;
    }

    protected function collectAttrData($node, &$customFieldData)
    {
        $attrData = [];
        foreach ($node->childNodes as $attrNode) {
            if (!$attrNode instanceof \DOMElement || !in_array($attrNode->nodeName, ['name', 'value'])) {
                continue;
            }
            $attrData[$attrNode->nodeName] = $attrNode->nodeValue;
        }
        if (!array_key_exists('name', $attrData) || !array_key_exists('value', $attrData)) {
            return;
        }
        $customFieldData[$node->nodeName][$attrData['name']] = $attrData['value'];
    }

    protected function collectStatusesData($node, &$customFieldData)
    {
        $statusesData = [];
        foreach ($node->childNodes as $statusNode) {
            if (!$statusNode instanceof \DOMElement || $statusNode->nodeName != 'id') {
                continue;
            }
            $statusesData[] = $statusNode->nodeValue;
        }
        $customFieldData[$node->nodeName] = $statusesData;
    }

    protected function collectOptionData($node, &$customFieldData)
    {
        $optionData = [];
        foreach ($node->childNodes as $optionNode) {
            if (!$optionNode instanceof \DOMElement || !in_array($optionNode->nodeName, ['value', 'default'])) {
                continue;
            }
            $optionData[$optionNode->nodeName] = $optionNode->nodeValue;
        }
        if (!array_key_exists('value', $optionData) || !array_key_exists('default', $optionData)) {
            return;
        }
        $customFieldData[$node->nodeName][$optionData['value']] = $optionData['default'];
    }
}
