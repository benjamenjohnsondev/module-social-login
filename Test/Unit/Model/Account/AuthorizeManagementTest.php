<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Test\Unit\Model\Account;

use BenJohnsonDev\SocialLogin\Model\Account\AuthorizeManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizeManagementTest extends TestCase
{
    private AuthorizeManagement $subject;
    private TimezoneInterface&MockObject $timezone;
    private Session&MockObject $customerSession;
    private CustomerRepositoryInterface&MockObject $customerRepository;
    private ManagerInterface&MockObject $messageManager;
    private EncryptorInterface&MockObject $encryptor;

    protected function setUp(): void
    {
        $this->timezone = $this->createMock(TimezoneInterface::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);

        $this->subject = new AuthorizeManagement(
            $this->timezone,
            $this->customerSession,
            $this->customerRepository,
            $this->messageManager,
            $this->encryptor,
        );
    }

    public function testAuthorizeUserDoesNothingWhenTokenNotExpired(): void
    {
        $customer = $this->buildCustomer(time() + 3600);

        $this->timezone->method('date')->willReturn(new \DateTime());

        $this->customerSession->expects($this->never())->method('logout');
        $this->messageManager->expects($this->never())->method('addErrorMessage');

        $this->subject->authorizeUser($customer);
    }

    public function testAuthorizeUserLogsOutWhenTokenExpired(): void
    {
        $customer = $this->buildCustomer(time() - 1);

        $date = new \DateTime();
        $this->timezone->method('date')->willReturn($date);

        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->customerSession->expects($this->once())->method('logout');

        $this->subject->authorizeUser($customer);
    }

    public function testAuthorizeUserLogsOutWhenTokenExpiryIsZero(): void
    {
        $customer = $this->buildCustomer(0);

        $this->timezone->method('date')->willReturn(new \DateTime());

        $this->customerSession->expects($this->once())->method('logout');

        $this->subject->authorizeUser($customer);
    }

    private function buildCustomer(int $tokenExpire): Customer&MockObject
    {
        $customer = $this->createMock(Customer::class);
        $customer->method('getTokenExpire')->willReturn((string) $tokenExpire);
        return $customer;
    }
}
