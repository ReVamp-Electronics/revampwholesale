<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\Status\ConfigDefault\Converter;

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

        $statuses = $source->getElementsByTagName('status');
        foreach ($statuses as $status) {
            $statusData = [];
            /** @var $status \DOMElement */
            foreach ($status->childNodes as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }
                /** @var $status \DOMElement */
                if ($child->nodeName == 'attribute') {
                    $attrData = [];
                    foreach ($child->childNodes as $attrNode) {
                        if (!$attrNode instanceof \DOMElement || !in_array($attrNode->nodeName, ['name', 'value'])) {
                            continue;
                        }
                        $attrData[$attrNode->nodeName] = $attrNode->nodeValue;
                    }
                    if (!array_key_exists('name', $attrData) || !array_key_exists('value', $attrData)) {
                        continue;
                    }
                    $statusData[$child->nodeName][$attrData['name']] = $attrData['value'];
                } else {
                    $statusData[$child->nodeName] = $child->nodeValue;
                }
            }
            $output[] = $statusData;
        }
        return $output;
    }
}
