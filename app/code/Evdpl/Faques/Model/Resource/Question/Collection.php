<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Evdpl\Faques\Model\Resource\Question;
 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Evdpl\Faques\Model\Question', 'Evdpl\Faques\Model\Resource\Question');
        //$this->_map['fields']['page_id'] = 'main_table.page_id';
    }
 
    
}
