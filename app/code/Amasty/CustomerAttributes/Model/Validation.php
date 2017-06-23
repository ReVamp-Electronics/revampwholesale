<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails\CollectionFactory as RelationDetailsCollectionFactory;

class Validation
{
    /**
     * @var \Magento\Framework\App\ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $rootDirectory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $dirReader;

    /**
     * @var RelationDetailsCollectionFactory
     */
    private $relationCollectionFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Module\Dir\Reader $dirReader,
        RelationDetailsCollectionFactory $relationCollectionFactory
    ) {
        $this->objectManager = $objectManager;
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->dirReader = $dirReader;
        $this->relationCollectionFactory = $relationCollectionFactory;
    }

    public function validateAttributeRelations(array $attributes)
    {
        /** @var \Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails\Collection $collection */
        $collection = $this->relationCollectionFactory->create()->joinDependAttributeCode();
        $attributesToSave = [];
        /** @var \Amasty\CustomerAttributes\Model\RelationDetails $relation */
        foreach ($collection as $relation) {
            foreach ($attributes as $attributeData) {
                // is attribute have relations
                if ($relation->getData('parent_attribute_code') == $attributeData->getAttributeCode()) {
                    $code = $relation->getData('dependent_attribute_code');
                    /**
                     * Is not to show - hide;
                     * false - value should to be saved
                     */
                    $attributesToSave[$code] = (bool)(isset($attributesToSave[$code]) && $attributesToSave[$code])
                        || $relation->getOptionId() == $attributeData->getValue()
                        || in_array($relation->getOptionId(), explode(',', $attributeData->getValue()));
                }
            }
        }
        $attributesToSave = $this->validateNestedRelations($attributesToSave, $collection);
        foreach ($attributes as $attributeData) {
            $code = $attributeData->getAttributeCode();
            if (array_key_exists($code, $attributesToSave) && !$attributesToSave[$code]) {
                $attributeData->setValue('');
            }
        }

        return $attributes;
    }

    /**
     * Check relation chain.
     * Example: we have
     *      relation1 - attribute1 = someAttribute1, dependAttribute1 = hidedSelect1
     *      relation2 - attribute2 = hidedSelect1, dependAttribute2 = someAttribute2
     *  where relation1.dependAttribute1 == relation2.attribute2
     *
     * @param array $isValidArray
     * @param \Amasty\CustomerAttributes\Model\ResourceModel\RelationDetails\Collection $relations
     *
     * @return array
     */
    public function validateNestedRelations($isValidArray, $relations)
    {
        $isNestedFind = false;
        foreach ($relations as $relation) {
            $parentCode = $relation->getData('parent_attribute_code');
            $dependCode = $relation->getData('dependent_attribute_code');
            if (array_key_exists($parentCode, $isValidArray) && !$isValidArray[$parentCode]
                && (!array_key_exists($dependCode, $isValidArray) || $isValidArray[$dependCode])
            ) {
                $isValidArray[$dependCode] = false;
                $isNestedFind = true;
            }
        }
        if ($isNestedFind) {
            $isValidArray = $this->validateNestedRelations($isValidArray, $relations);
        }

        return $isValidArray;
    }

    /**
     * Retrieve additional validation types
     *
     * @return array
     */
    public function getAdditionalValidation()
    {
        $addon = [];
        $files = $this->_getValidationFiles();
        foreach ($files as $file) {
            if (false !== strpos($file, '.php')) {
                $addon[] = $this->objectManager->create(
                    'Amasty\CustomerAttributes\Model\Validation\\'
                    . str_replace('.php', '', $file)
                )->getValues();
            }
        }
        return $addon;
    }

    protected function _getValidationFiles()
    {
        $path = $this->dirReader->getModuleDir('', 'Amasty_CustomerAttributes') . DIRECTORY_SEPARATOR . 'Model'
            . DIRECTORY_SEPARATOR . 'Validation';
        $files = scandir($path);
        return $files;
    }

    /**
     * Retrieve JS code
     *
     * @return string
     */
    public function getJS()
    {
        $js = '';
        $files = $this->_getValidationFiles();
        foreach ($files as $file) {
            if (false !== strpos($file, '.php')) {
                $js .= $this->objectManager->create(
                    'Amasty\CustomerAttributes\Model\Validation\\'
                    . str_replace('.php', '', $file)
                )->getJS();
            }
        }
        return $js;
    }
}
