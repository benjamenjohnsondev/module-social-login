<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Account;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\Data\CustomerInterface;

interface CreateManagementInterface
{
    /**
     * Create customer account
     *
     * @param \League\OAuth2\Client\Provider\ResourceOwnerInterface $user
     * @param \League\OAuth2\Client\Token\AccessTokenInterface $accessToken
     * @param string $redirectUrl
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createAccount(
        ResourceOwnerInterface $user,
        AccessTokenInterface $accessToken,
        string $redirectUrl = ''
    ): CustomerInterface;
}
