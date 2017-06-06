<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Model;

use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface;

/**
 * Class Gateway
 * @package Aheadworks\Helpdesk\Model
 */
class Gateway extends \Magento\Framework\DataObject
{
    /**
     * Connection
     * @var null | \Zend_Mail_Storage_Imap | \Zend_Mail_Storage_Pop3
     */
    private $connection;

    /**
     * Protocol source
     * @var \Aheadworks\Helpdesk\Model\Source\Gateway\Protocol
     */
    private $protocolSource;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Source\Gateway\Protocol $protocolSource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Aheadworks\Helpdesk\Model\Source\Gateway\Protocol $protocolSource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->protocolSource = $protocolSource;
        $this->storeManager = $storeManager;

        parent::__construct($data);
    }

    /**
     * Get gateway connection
     *
     * @param DepartmentGatewayInterface $gateway
     * @return null | \Zend_Mail_Storage_Imap | \Zend_Mail_Storage_Pop3
     */
    public function getConnection($gateway)
    {
        $this->initConnectionByGateway($gateway);
        return $this->connection;
    }

    /**
     * Test connection
     *
     * @param array $params
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testConnection($params)
    {
        $instanceConstructor = $this->protocolSource->getInstanceByProtocol($params['protocol']);
        $instance = new $instanceConstructor($params);

        return $this;
    }

    /**
     * Init connection by gateway data object
     *
     * @param DepartmentGatewayInterface $gateway
     * @return $this
     */
    private function initConnectionByGateway($gateway)
    {
        $protocol = $gateway->getProtocol();
        $instanceConstructor = $this->protocolSource->getInstanceByProtocol($protocol);
        if (null !== $instanceConstructor) {
            $params = [
                'host'      => $gateway->getHost(),
                'protocol'  => $gateway->getProtocol(),
                'user'      => $gateway->getLogin(),
                'password'  => $gateway->getPassword(),
                'port'      => $gateway->getPort(),
                'ssl'       => $gateway->getSecureType()
            ];

            try {
                $this->connection = new $instanceConstructor($params);
            } catch (\Exception $e) {
                $this->connection = null;
            }
        }
        return $this;
    }
}
