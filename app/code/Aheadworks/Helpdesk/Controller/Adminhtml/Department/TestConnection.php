<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Controller\Adminhtml\Department;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Aheadworks\Helpdesk\Model\Gateway as GatewayModel;

/**
 * Class TestConnection
 * @package Aheadworks\Helpdesk\Controller\Adminhtml\Department
 */
class TestConnection extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Helpdesk::departments';

    /**
     * @var GatewayModel
     */
    private $gatewayModel;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param GatewayModel $gatewayModel
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        GatewayModel $gatewayModel
    ) {
        $this->gatewayModel = $gatewayModel;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}*
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $gatewayParams = $this->getRequest()->getParam('gateway_data');

        $params = [
            'host'      => $gatewayParams['host'],
            'protocol'  => $gatewayParams['protocol'],
            'user'      => $gatewayParams['login'],
            'password'  => $gatewayParams['password'],
            'port'      => $gatewayParams['port'],
            'ssl'       => $gatewayParams['secure_type']
        ];
        try {
            $this->gatewayModel->testConnection($params);
            $result = [
                'valid'     => true,
                'message'   => __('Success.')
            ];
        } catch (\Exception $e) {
            $result = [
                'valid'     => false,
                'message'   => __($e->getMessage())
            ];
        }
        return $resultJson->setData($result);
    }
}
