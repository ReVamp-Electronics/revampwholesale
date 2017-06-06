<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

/**
 * Class MassDisable
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
class MassDisable extends \Aheadworks\Helpdesk\Controller\Adminhtml\Department\MassAbstract
{
    /**
     * @var string
     */
    protected $errorMessage = 'Something went wrong while disabling department(s)';

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
                if ($departmentDataObject->getIsDefault()) {
                    $this->messageManager->addErrorMessage(
                        __('Default department %1 can not be disabled', $departmentDataObject->getName())
                    );
                    continue;
                }
                $departmentDataObject->setIsEnabled(false);
                $this->departmentRepository->save($departmentDataObject);
                $count++;
            }
        }
        if ($count > 0) {
            $this->messageManager->addSuccessMessage(__('A total of %1 department(s) have been disabled', $count));
        }
    }
}
