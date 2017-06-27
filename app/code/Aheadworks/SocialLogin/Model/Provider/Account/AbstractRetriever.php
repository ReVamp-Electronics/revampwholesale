<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account;

use Aheadworks\SocialLogin\Exception\InvalidRetrievedDataException;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface;

abstract class AbstractRetriever implements RetrieverInterface
{
    /**
     * @var \Aheadworks\SocialLogin\Model\Provider\AccountFactory
     */
    protected $accountFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @param \Aheadworks\SocialLogin\Model\Provider\AccountFactory $accountFactory
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Aheadworks\SocialLogin\Model\Provider\AccountFactory $accountFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->accountFactory = $accountFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param ServiceInterface $service
     * @return AccountInterface
     */
    public function retrieve(ServiceInterface $service)
    {
        $responseData = $this->requestData($service);
        $accountData = $this->prepareResponseData($responseData);

        $account = $this->createAccount()->setData($accountData);
        $this->assertAccount($account);

        return $account;
    }

    /**
     * Assert retrieved account data
     *
     * @param AccountInterface $account
     * @return $this
     * @throws InvalidRetrievedDataException
     */
    protected function assertAccount(AccountInterface $account)
    {
        if (empty($account->getSocialId()) || empty($account->getType())) {
            throw new InvalidRetrievedDataException(__('Retrieved invalid data'));
        }
        return $this;
    }

    /**
     * Request data
     *
     * @param ServiceInterface $service
     * @return mixed
     */
    abstract protected function requestData(ServiceInterface $service);

    /**
     * Prepare response data
     *
     * @param \Magento\Framework\DataObject $responseData
     * @return array
     */
    abstract protected function prepareResponseData(\Magento\Framework\DataObject $responseData);

    /**
     * Create account object
     *
     * @return AccountInterface
     */
    protected function createAccount()
    {
        return $this->accountFactory->create();
    }

    /**
     * Decode json to array
     *
     * @param string $jsonStr
     * @return array
     */
    protected function decodeJson($jsonStr)
    {
        return json_decode($jsonStr, true);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function createDataObject()
    {
        return $this->dataObjectFactory->create();
    }
}
