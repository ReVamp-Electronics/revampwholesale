<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model;

use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;

/**
 * Class DepartmentRegistry
 * @package Aheadworks\Helpdesk\Model
 */
class DepartmentRegistry
{
    /**
     * @var DepartmentInterface[]
     */
    private $departmentRegistry = [];

    /**
     * Retrieve department data model by department id
     *
     * @param int $departmentId
     * @return DepartmentInterface|null
     */
    public function retrieve($departmentId)
    {
        if (isset($this->departmentRegistry[$departmentId])) {
            return $this->departmentRegistry[$departmentId];
        }
        return null;
    }

    /**
     * Remove department data model from registry by department id
     *
     * @param int $departmentId
     * @return void
     */
    public function remove($departmentId)
    {
        if (isset($this->departmentRegistry[$departmentId])) {
            unset($this->departmentRegistry[$departmentId]);
        }
    }

    /**
     * Replace existing department data model with a new one
     *
     * @param DepartmentInterface $departmentDataObject
     * @return $this
     */
    public function push(DepartmentInterface $departmentDataObject)
    {
        if ($departmentDataObject->getId()) {
            $this->departmentRegistry[$departmentDataObject->getId()] = $departmentDataObject;
        }
        return $this;
    }
}
