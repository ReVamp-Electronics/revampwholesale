<?php
namespace Aheadworks\SocialLogin\Test\Block\Customer\Account;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;

/**
 * Class LinkedAccounts
 */
class LinkedAccounts extends Block
{
    /**
     * @var string
     */
    protected $accountItem = 'div.social-account-item';

    /**
     * @var string
     */
    protected $accountItemProvider = 'span.social-account-%s';

    /**
     * @var string
     */
    protected $accountItemUnlink = 'div.social-account-unlink';

    /**
     * Unlink account
     *
     * @param $providerCode
     */
    public function unlink($providerCode)
    {
        $account = $this->getProviderAccount($providerCode);
        $account->find($this->accountItemUnlink)->click();
    }

    /**
     * Is provider account exist
     *
     * @param string $providerCode
     * @return bool
     */
    public function isAccountExist($providerCode)
    {
        try {
            $account = $this->getProviderAccount($providerCode);
            $providerLabel = $account->find(sprintf($this->accountItemProvider, $providerCode));
            $isExist = $providerLabel->isVisible();
        } catch (\Exception $e) {
            $isExist = false;
        }

        return $isExist;
    }

    /**
     * @param string $providerCode
     * @return ElementInterface
     * @throws \Exception
     */
    public function getProviderAccount($providerCode)
    {
        $providerAccount = null;

        /** @var ElementInterface $item */
        foreach ($this->getAccountItems() as $item) {
            $providerLabel = $item->find(sprintf($this->accountItemProvider, $providerCode));
            if ($providerLabel->isVisible()) {
                $providerAccount = $item;
            }
        }

        if (!$providerAccount) {
            throw new \Exception('Provider account not exist');
        }

        return $providerAccount;
    }

    /**
     * @return \Magento\Mtf\Client\ElementInterface
     */
    protected function getAccountItems()
    {
        return $this->_rootElement->getElements($this->accountItem);
    }
}
