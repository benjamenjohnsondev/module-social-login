<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Observer;

use BenJohnsonDev\SocialLogin\Api\Account\AuthorizeManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AuthorizeUser implements ObserverInterface
{
    public function __construct(
        protected Session $customerSession,
        protected AuthorizeManagementInterface $authorizeManagement
    ) {
    }

    /**
     * Observer for controller_front_send_response_before
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        // If the customer is not logged in, return
        if (!$this->customerSession->isLoggedIn()) {
            return;
        }

        // Get the current customer
        $customer = $this->customerSession->getCustomer();

        //If the customer uses a social login, check if the user is authorized
        if ($customer->getProvider() !== null ||
            $customer->getProvider() !== 'revoked'
        ) {
            // Void method - logs user out if token is expired/invalid
            $this->authorizeManagement->authorizeUser($customer);
        }
    }
}
