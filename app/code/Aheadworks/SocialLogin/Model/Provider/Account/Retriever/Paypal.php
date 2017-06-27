<?php
namespace Aheadworks\SocialLogin\Model\Provider\Account\Retriever;

use Aheadworks\SocialLogin\Exception\InvalidRetrievedDataException;
use Aheadworks\SocialLogin\Model\Provider\Account\AbstractRetriever;
use Aheadworks\SocialLogin\Model\Provider\AccountInterface;
use Aheadworks\SocialLogin\Model\Provider\Service\ServiceInterface;
use Magento\Framework\DataObject;

/**
 * Class Paypal.
 */
class Paypal extends AbstractRetriever
{
    /**
     * Get account method
     */
    const API_METHOD_ACCOUNT_GET = 'identity/openidconnect/userinfo/?schema=openid';

    /**
     * {@inheritdoc}
     */
    protected function requestData(ServiceInterface $service)
    {
        /** @var \Aheadworks\SocialLogin\Model\Provider\Service\Paypal $service */
        $response = $service->request(self::API_METHOD_ACCOUNT_GET);

        $responseData = $this->decodeJson($response);

        return $this->createDataObject()->setData($responseData);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareResponseData(DataObject $responseData)
    {
        return [
            AccountInterface::TYPE => AccountInterface::TYPE_PAYPAL,
            AccountInterface::SOCIAL_ID => $this->fetchSocialId($responseData),
            AccountInterface::FIRST_NAME => $responseData->getData('given_name'),
            AccountInterface::LAST_NAME => $responseData->getData('family_name'),
            AccountInterface::EMAIL => $responseData->getData('email')
        ];
    }

    /**
     * Fetch social id.
     *
     * @param DataObject $responseData
     * @return string
     * @throws InvalidRetrievedDataException
     */
    private function fetchSocialId(DataObject $responseData)
    {
        $userId = (string)$responseData->getData('user_id');

        $parts = explode('user/', $userId);

        if (!isset($parts[1])) {
            throw new InvalidRetrievedDataException(__('Retrieved invalid data'));
        }

        return $parts[1];
    }
}
