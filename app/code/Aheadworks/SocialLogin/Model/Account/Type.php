<?php
namespace Aheadworks\SocialLogin\Model\Account;

use Aheadworks\SocialLogin\Model\ProviderManagement;

/**
 * Class Type
 */
class Type implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var ProviderManagement
     */
    protected $providerManagement;

    /**
     * @param ProviderManagement $providerManagement
     */
    public function __construct(ProviderManagement $providerManagement)
    {
        $this->providerManagement = $providerManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->providerManagement->getList() as $providerFactory) {
            $options[] = [
                'label' => $providerFactory->getConfig()->getTitle(),
                'value' => $providerFactory->getConfig()->getCode()
            ];
        }
        return $options;
    }
}
