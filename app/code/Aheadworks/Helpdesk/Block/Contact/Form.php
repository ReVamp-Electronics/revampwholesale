<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Block\Contact;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Helpdesk\Model\Source\Ticket\DepartmentFrontend as DepartmentFrontendSource;

/**
 * Class Form
 * @package Aheadworks\Helpdesk\Block\Contact
 */
class Form extends Template
{
    /**
     * @var DepartmentFrontendSource
     */
    private $departmentFrontendSource;

    /**
     * @var string[]
     */
    private $departments;

    /**
     * @param Context $context
     * @param DepartmentFrontendSource $departmentFrontendSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        DepartmentFrontendSource $departmentFrontendSource,
        array $data = []
    ) {
        $this->departmentFrontendSource = $departmentFrontendSource;
        parent::__construct($context, $data);
    }

    /**
     * Get departments as array
     *
     * @return bool|string[]
     */
    public function getDepartments()
    {
        if (!$this->departments) {
            $this->departments = $this->departmentFrontendSource->getOptions();
        }
        return $this->departments;
    }
}
