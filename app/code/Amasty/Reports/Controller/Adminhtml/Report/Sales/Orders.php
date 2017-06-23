<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report\Sales;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;

class Orders extends ReportController
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_sales_orders');
    }

    public function execute()
    {
        $resultPage = $this->prepareResponse();

        if ($resultPage instanceof \Magento\Backend\Model\View\Result\Page) {
            $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
        }

        return $resultPage;
    }
}
