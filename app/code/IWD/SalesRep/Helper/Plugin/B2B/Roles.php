<?php

namespace IWD\SalesRep\Helper\Plugin\B2B;

/**
 * Class Roles
 * @package IWD\SalesRep\Helper\Plugin\B2B
 */
class Roles
{
    use B2BHelperTrait;

    public function afterCheckRoleAccess($subject, $res)
    {
        if ($this->isB2BInstalled() && $res) {
            if ($this->isSalesrepLoggedInAsCustomer()) {
                $res = false;
            }
        }

        return $res;
    }
}
