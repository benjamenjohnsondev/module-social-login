<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Account;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;

interface AuthorizeManagementInterface
{
    /**
     * Authorizes user
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer $customer
     * @return void
     */
    public function authorizeUser(CustomerInterface|Customer $customer): void;
}
