<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report\Sales;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;
use Magento\Backend\Model\View\Result\Page;

class Hour extends ReportController
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_sales_hour');
    }
    
    public function execute()
    {
        $resultPage = $this->prepareResponse();
        
        if ($resultPage instanceof Page) {
            $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        }

        return $resultPage;
    }
}
