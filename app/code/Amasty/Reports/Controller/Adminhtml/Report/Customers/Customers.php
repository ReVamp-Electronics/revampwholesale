<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report\Customers;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;
use Magento\Backend\Model\View\Result\Page;

class Customers extends ReportController
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_customers_customers');
    }
    
    public function execute()
    {
        $resultPage = $this->prepareResponse();
        
        if ($resultPage instanceof Page) {
            $resultPage->addBreadcrumb(__('Customers'), __('Customers'));
        }

        return $resultPage;
    }
}
