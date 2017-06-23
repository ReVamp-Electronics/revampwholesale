<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_CustomerAttributes
 */
namespace Amasty\CustomerAttributes\Plugin\Quote;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;

class Quote
{
    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var Session
     */
    protected $_sessionHelper;
    /**
     * @var \Amasty\CustomerAttributes\Model\Validation
     */
    private $validation;

    /**
     * Quote constructor.
     *
     * @param Session                                     $customerSession
     * @param AccountManagementInterface                  $customerAccountManagement
     * @param CustomerRepositoryInterface                 $customerRepository
     * @param CustomerExtractor                           $customerExtractor
     * @param \Magento\Framework\Api\DataObjectHelper     $dataObjectHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Amasty\CustomerAttributes\Helper\Session   $sessionHelper
     */
    public function __construct(
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerRepositoryInterface $customerRepository,
        CustomerExtractor $customerExtractor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Amasty\CustomerAttributes\Helper\Session $sessionHelper,
        \Amasty\CustomerAttributes\Model\Validation $validation
    ) {
        $this->session                   = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerRepository        = $customerRepository;
        $this->customerExtractor         = $customerExtractor;
        $this->dataObjectHelper          = $dataObjectHelper;
        $this->messageManager            = $messageManager;
        $this->_sessionHelper            = $sessionHelper;
        $this->validation = $validation;
    }

    public function beforeSetShippingAddress(
        $subject,
        $address = null
    ) {
        $customAttributes = $address->getCustomAttributes();
        if ($customAttributes) {
            $customAttributes = $this->validation->validateAttributeRelations($customAttributes);
            $customerId = $this->session->getCustomerId();
            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
                $customer->setCustomAttributes($customAttributes);

                try {
                    $this->customerRepository->save($customer);
                } catch (AuthenticationException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (InputException $e) {
                    $this->messageManager->addException($e, __('Invalid input'));
                } catch (\Exception $e) {
                    $message = __('We can\'t save the customer.')
                        . $e->getMessage()
                        . '<pre>' . $e->getTraceAsString() . '</pre>';
                    $this->messageManager->addException($e, $message);
                }
            } else {
                // TODO: Save to Quote, don't use session;
                $this->_sessionHelper->setCustomerAttributesToSession($customAttributes);
            }
        }

        return [$address];
    }
}
