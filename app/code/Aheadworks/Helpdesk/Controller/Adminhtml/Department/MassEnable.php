<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

/**
 * Class MassEnable
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
class MassEnable extends \Aheadworks\Helpdesk\Controller\Adminhtml\Department\MassAbstract
{
    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while enabling department(s)';

    /**
     * {@inheritdoc}
     */
    protected function massAction($collection)
    {
        $count = 0;
        /** @var \Aheadworks\Helpdesk\Model\Department $department */
        foreach ($collection->getItems() as $department) {
            /** @var \Aheadworks\Helpdesk\Api\Data\DepartmentInterface $departmentDataObject */
            $departmentDataObject = $this->departmentRepository->getById($department->getId());
            if ($departmentDataObject->getId()) {
                $departmentDataObject->setIsEnabled(true);
                $this->departmentRepository->save($departmentDataObject);
                $count++;
            }
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 department(s) have been enabled', $count));
    }
}
