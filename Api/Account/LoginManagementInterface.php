<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Account;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Controller\Result\Redirect;

interface LoginManagementInterface
{
    /**
     * Log in customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \League\OAuth2\Client\Token\AccessTokenInterface $accessToken
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterface $provider
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function loginCustomer(
        CustomerInterface $customer,
        AccessTokenInterface $accessToken,
        ProviderInterface $provider
    ): Redirect;
}
