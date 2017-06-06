<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model\Website;

use Aheadworks\Helpdesk\Api\DepartmentRepositoryInterface;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterfaceFactory;
use Aheadworks\Helpdesk\Api\Data\DepartmentInterface;
use Magento\Store\Model\Website as WebsiteModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class WebsitePlugin
 * @package Aheadworks\Helpdesk\Model\Website
 */
class WebsitePlugin
{
    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepository;

    /**
     * @var DepartmentInterfaceFactory
     */
    private $departmentInterfaceFactory;

    /**
     * @param DepartmentRepositoryInterface $departmentRepository
     * @param DepartmentInterfaceFactory $departmentInterfaceFactory
     */
    public function __construct(
        DepartmentRepositoryInterface $departmentRepository,
        DepartmentInterfaceFactory $departmentInterfaceFactory
    ) {
        $this->departmentRepository = $departmentRepository;
        $this->departmentInterfaceFactory = $departmentInterfaceFactory;
    }

    /**
     * Create default department for new website
     *
     * @param WebsiteModel $subject
     * @param \Closure $proceed
     * @return WebsiteModel
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function aroundSave(WebsiteModel $subject, \Closure $proceed)
    {
        $result = $proceed();

        if ($result) {
            $websiteId = $subject->getWebsiteId();
            $websiteName = $subject->getName();
            try {
                /** @var DepartmentInterface $department */
                $department = $this->departmentRepository->getDefaultByWebsiteId($websiteId);
                $this->departmentRepository->getDefaultByWebsiteId($websiteId);
            } catch (LocalizedException $e) {
                /** @var DepartmentInterface $department */
                $department = $this->departmentInterfaceFactory->create();
                $department
                    ->setName('General-' . $websiteName)
                    ->setIsEnabled(true)
                    ->setIsVisible(false)
                    ->setIsDefault(true)
                    ->setWebsiteIds([$websiteId]);
                $this->departmentRepository->save($department);
            }
        }

        return $result;
    }

    /**
     * Remove Default department parameter for removed website
     *
     * @param WebsiteModel $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundDelete(WebsiteModel $subject, \Closure $proceed)
    {
        $websiteId = $subject->getWebsiteId();
        try {
            /** @var DepartmentInterface $department */
            $department = $this->departmentRepository->getDefaultByWebsiteId($websiteId);
        } catch (LocalizedException $e) {

        }

        $result = $proceed();

        if ($result) {
            if ($department && count($department->getWebsiteIds()) > 1) {
                $websiteIds = $department->getWebsiteIds();
                foreach ($websiteIds as $key => $value) {
                    if ($value = $websiteId) {
                        unset($websiteIds[$key]);
                        break;
                    }
                }
                $department->setWebsiteIds($websiteIds);
                $this->departmentRepository->save($department);
            } else {
                $department->setIsDefault(false);
                $department->setWebsiteIds([]);
                $this->departmentRepository->save($department);
            }
        }

        return $result;
    }
}
