<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */

namespace Amasty\CustomerAttributes\Helper;

class Collection
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    protected $objectManager;

    protected $_memberAlias = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
        $this->resource = $resource;
    }

    public function addFilters($collection, $tableName, $filters = [], $sorting = null)
    {
        $aliasDefault = $this->getProperAlias($collection->getSelect()->getPart('from'), $tableName);
        $select = $collection->getSelect();
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $key = '';
                $where = '';
                if (is_array($filter) && isset($filter['key'])) {
                    $key = $filter['key'];
                    unset($filter['key']);
                    $len = count($filter);
                    $i = 1;
                    foreach ($filter as $val) {
                        $rKey = '';
                        if ($len > $i++) {
                            $rKey = $key;
                        }
                        if (is_array($val)) {
                            $this->checkInterface($val);

                            if (isset($val['table'])) {
                                $alias = $this->getProperAlias(
                                    $collection->getSelect()->getPart('from'),
                                    $val['table']
                                );
                            } else {
                                $alias = $aliasDefault;
                            }
                            $where .= ' ' . $alias . $val['cond']
                                . $val['value']
                                . ' ' . $rKey;
                        } else {
                            $alias = $aliasDefault;
                            $where .= ' ' . $alias . $val . ' ' . $rKey;
                        }
                    }
                } elseif (is_array($filter)) {
                    $this->checkInterface($filter);
                    if (isset($filter['table'])) {
                        $alias = $this->getProperAlias(
                            $collection->getSelect()->getPart('from'),
                            $filter['table']
                        );
                    } else {
                        $alias = $aliasDefault;
                    }
                    $where = ' ' . $alias . $filter['cond'] . $filter['value'];

                } else {
                    $where = $aliasDefault . $filter;
                }
                if (!empty($where)) {
                    $select->where($where);
                }
            }
        }

        if ($sorting) {
            $select->order($aliasDefault . $sorting);
        }

        return $collection;
    }

    public function getProperAlias($from, $needTableName)
    {
        $needTableName = $this->resource->getTableName($needTableName);
        $key = serialize($from) . $needTableName;
        if (isset($this->_memberAlias[$key])) {
            return $this->_memberAlias[$key];
        }

        foreach ($from as $key => $table) {
            $fullTableName = explode('.', $table['tableName']);
            if (isset($fullTableName[1])) {
                $tableName = $fullTableName[1];
            } else {
                $tableName = $fullTableName[0];
            }
            if ($needTableName == $tableName) {
                return $key . '.';
            }
        }
        return '';
    }

    public function checkInterface($value)
    {
        if (!isset($value['cond']) || !isset($value['value'])) {
            throw new \Exception(__('Amasty error. Bad filter for select'));
        }
    }

    public function getAttributesHash()
    {
        $collection = $this->objectManager->get('Magento\Customer\Model\Attribute')->getCollection();


        $filters = array(
            "is_user_defined = 1",
            "frontend_input != 'file' ",
            "frontend_input != 'multiselect' "
        );
        $collection = $this->addFilters(
            $collection, 'eav_attribute', $filters

        );

        $filters = array(
            array(
                "key" => "OR",
               // "type_internal = 'statictext' ",
                array(
                    'cond'  => 'backend_type =',
                    'value' => "'varchar'",
                    'table' => 'eav_attribute'
                )
            )
        );
        $collection = $this->addFilters(
            $collection, 'customer_eav_attribute', $filters
        );

        $attributes = $collection->load();
        $hash = array();
        foreach ($attributes as $attribute) {
            $hash[$attribute->getAttributeCode()]
                = $attribute->getFrontendLabel();
        }
        return $hash;
    }
}