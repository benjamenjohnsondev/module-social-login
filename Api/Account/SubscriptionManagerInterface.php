<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Api\Account;

interface SubscriptionManagerInterface
{
    /**
     * Subscribe a customer to the newsletter
     *
     * @param int $customerId
     * @return void
     */
    public function subscribe(int $customerId): void;
}
