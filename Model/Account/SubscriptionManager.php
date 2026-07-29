<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\Account;

use BenJohnsonDev\SocialLogin\Api\Account\SubscriptionManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;

class SubscriptionManager implements SubscriptionManagerInterface
{
    /**
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        private readonly SubscriberFactory $subscriberFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function subscribe(int $customerId): void
    {
        $this->subscriberFactory->create()->subscribeCustomerById($customerId);
    }
}
