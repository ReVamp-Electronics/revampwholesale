<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Rma\Model\CustomField\ConfigDefault\Reader;

/**
 * Class Xml
 * @package Aheadworks\Rma\Model\CustomField\ConfigDefault\Reader
 */
class Xml extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param \Aheadworks\Rma\Model\CustomField\ConfigDefault\Converter\Xml $converter
     * @param \Aheadworks\Rma\Model\CustomField\ConfigDefault\SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        \Magento\Framework\Config\FileResolverInterface $fileResolver,
        \Aheadworks\Rma\Model\CustomField\ConfigDefault\Converter\Xml $converter,
        \Aheadworks\Rma\Model\CustomField\ConfigDefault\SchemaLocator $schemaLocator,
        \Magento\Framework\Config\ValidationStateInterface $validationState,
        $fileName = 'rma_custom_fields.xml',
        $idAttributes = [],
        $domDocumentClass = 'Magento\Framework\Config\Dom',
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
