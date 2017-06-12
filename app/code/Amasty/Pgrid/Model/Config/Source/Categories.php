<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

namespace Amasty\Pgrid\Model\Config\Source;

class Categories implements \Magento\Framework\Option\ArrayInterface
{
    protected $category;

    public function __construct(
        \Magento\Catalog\Model\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach($arr as $value => $label){
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    protected function _buildPath($category)
    {
        if ($category->getName()) // main root category will have no name, so we'll not add it
        {
            $this->_path[] = array(
                'id'    => $category->getId(),
                'level' => $category->getLevel(),
                'name'  => $category->getName(),
            );
        }
        if ($category->hasChildren())
        {
            foreach ($this->getChildrenCategories($category) as $child)
            {
                $this->_buildPath($child);
            }
        }
    }

    public function getChildrenCategories($category)
    {

        $collection = $category->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('all_children')
            ->addAttributeToSelect('is_anchor')
//            ->addAttributeToFilter('is_active', 1)
            ->addFieldToFilter('parent_id', $category->getId())
            ->setOrder('position', 'asc')
            ->joinUrlRewrite()
            ->load();

        return $collection;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $parentCategory = $this->category->load(\Magento\Catalog\Model\Category::TREE_ROOT_ID);

        $this->_buildPath($parentCategory);

        $options = array();
//        $options[0] = __('- With no category');
        foreach ($this->_path as $i => $path)
        {
            $string = str_repeat(". ", max(0, ($path['level'] - 1) * 3)) . $path['name'];
            $options[$path['id']] = $string;
        }

        return $options;
    }
}
