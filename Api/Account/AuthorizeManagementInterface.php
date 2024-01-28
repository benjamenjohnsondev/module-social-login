<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Account;

interface AuthorizeManagementInterface
{
    /**
     * Authorizes user
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|\Magento\Customer\Model\Customer $customer
     * @return void
     * @noinspection PhpMissingParamTypeInspection
     */
    public function authorizeUser($customer): void;
}
