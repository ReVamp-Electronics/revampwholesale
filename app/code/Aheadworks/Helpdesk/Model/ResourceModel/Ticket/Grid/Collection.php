<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid;

use Aheadworks\Helpdesk\Model\Ticket as TicketModel;
use Aheadworks\Helpdesk\Model\ResourceModel\Ticket as TicketResource;

/**
 * Class Collection
 * @package Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Grid
 */
class Collection extends \Aheadworks\Helpdesk\Model\ResourceModel\Ticket\Collection
{
    /**
     * Id field name
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(TicketModel::class, TicketResource::class);
        $this->addFilterToMap('agent_id', 'main_table.agent_id');
    }

    /**
     * Init select
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->joinTicketFlat();
        $this->joinDepartments();
        return $this;
    }

    /**
     * Join departments
     * @return $this
     */
    public function joinDepartments()
    {
        $this
            ->getSelect()
            ->join(
                ['department' => $this->getTable('aw_helpdesk_department')],
                'main_table.department_id = department.id',
                ['department_name' => 'department.name']
            );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'id') {
            $field = 'main_table.id';
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
