<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Test\Unit\Model\Account;

use BenJohnsonDev\SocialLogin\Model\Account\CreateManagement;
use BenJohnsonDev\SocialLogin\Model\Account\RandomPasswordGenerator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\Session;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime;
use Magento\Newsletter\Api\SubscriptionManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateManagementTest extends TestCase
{
    private CreateManagement $subject;
    private Session&MockObject $customerSession;
    private CustomerInterfaceFactory&MockObject $customerFactory;
    private CustomerRepositoryInterface&MockObject $customerRepository;
    private CustomerRegistry&MockObject $customerRegistry;
    private AccountManagementInterface&MockObject $accountManagement;
    private RandomPasswordGenerator&MockObject $randomPasswordGenerator;
    private Random&MockObject $mathRandom;
    private DateTime&MockObject $dateTime;
    private SubscriptionManagerInterface&MockObject $subscriptionManager;
    private StoreManagerInterface&MockObject $storeManager;
    private EncryptorInterface&MockObject $encryptor;

    protected function setUp(): void
    {
        $this->customerSession = $this->createMock(Session::class);
        $this->customerFactory = $this->createMock(CustomerInterfaceFactory::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerRegistry = $this->createMock(CustomerRegistry::class);
        $this->accountManagement = $this->createMock(AccountManagementInterface::class);
        $this->randomPasswordGenerator = $this->createMock(RandomPasswordGenerator::class);
        $this->mathRandom = $this->createMock(Random::class);
        $this->dateTime = $this->createMock(DateTime::class);
        $this->subscriptionManager = $this->createMock(SubscriptionManagerInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);

        $this->subject = new CreateManagement(
            $this->customerSession,
            $this->customerFactory,
            $this->customerRepository,
            $this->customerRegistry,
            $this->accountManagement,
            $this->randomPasswordGenerator,
            $this->mathRandom,
            $this->dateTime,
            $this->subscriptionManager,
            $this->storeManager,
            $this->encryptor,
        );
    }

    public function testCreateAccountNewCustomer(): void
    {
        $user = $this->buildUser('test@example.com', 'Jane', 'Doe', 'uid_123');
        $accessToken = $this->buildAccessToken('refresh_abc', time() + 3600);
        $customer = $this->createMock(CustomerInterface::class);

        $this->customerFactory->method('create')->willReturn($customer);
        $this->customerSession->method('getData')->willReturnMap([
            ['provider', false, 'google'],
            ['is_subscribed', false, '0'],
        ]);
        $customer->method('setFirstname')->willReturnSelf();
        $customer->method('setLastname')->willReturnSelf();
        $customer->method('setEmail')->willReturnSelf();
        $customer->method('setCustomAttribute')->willReturnSelf();
        $customer->method('getId')->willReturn(42);
        $customer->method('getStoreId')->willReturn(1);

        $this->encryptor->method('encrypt')->with('refresh_abc')->willReturn('encrypted_refresh_abc');
        $this->randomPasswordGenerator->method('generate')->willReturn('RandPass123!');
        $this->accountManagement->method('getPasswordHash')->willReturn('hashed');
        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->willReturn($customer);

        $this->subscriptionManager->expects($this->never())->method('subscribeCustomer');

        $result = $this->subject->createAccount($user, $accessToken);

        $this->assertSame($customer, $result);
    }

    public function testCreateAccountExistingCustomerByEmail(): void
    {
        $user = $this->buildUser('existing@example.com', 'John', 'Smith', 'uid_456');
        $accessToken = $this->buildAccessToken('refresh_def', time() + 3600);
        $newCustomer = $this->createMock(CustomerInterface::class);
        $existingCustomer = $this->createMock(CustomerInterface::class);
        $customerSecure = $this->createMock(CustomerSecure::class);

        $this->customerFactory->method('create')->willReturn($newCustomer);
        $this->customerSession->method('getData')->willReturnMap([
            ['provider', false, 'google'],
            ['is_subscribed', false, '0'],
        ]);
        $newCustomer->method('setFirstname')->willReturnSelf();
        $newCustomer->method('setLastname')->willReturnSelf();
        $newCustomer->method('setEmail')->willReturnSelf();
        $newCustomer->method('setCustomAttribute')->willReturnSelf();

        $this->randomPasswordGenerator->method('generate')->willReturn('RandPass123!');
        $this->accountManagement->method('getPasswordHash')->willReturn('hashed');
        $this->accountManagement->expects($this->once())
            ->method('createAccount')
            ->willThrowException(new InputMismatchException(__('Email exists')));

        $this->customerRepository->method('get')
            ->with('existing@example.com')
            ->willReturn($existingCustomer);
        $existingCustomer->method('getId')->willReturn(99);
        $existingCustomer->method('setCustomAttribute')->willReturnSelf();

        $this->mathRandom->method('getUniqueHash')->willReturn('rp_token_xyz');
        $this->dateTime->method('formatDate')->willReturn('2025-01-01 00:00:00');

        $customerSecure->method('setRpToken')->willReturnSelf();
        $customerSecure->method('setRpTokenCreatedAt')->willReturnSelf();
        $this->customerRegistry->method('retrieveSecureData')->willReturn($customerSecure);

        $this->accountManagement->expects($this->once())->method('resetPassword');
        $this->customerRepository->expects($this->once())->method('save')->with($existingCustomer);
        $this->encryptor->method('encrypt')->willReturn('encrypted_token');

        $result = $this->subject->createAccount($user, $accessToken);

        $this->assertSame($existingCustomer, $result);
    }

    public function testCreateAccountSubscribesWhenRequested(): void
    {
        $user = $this->buildUser('sub@example.com', 'Sub', 'User', 'uid_sub');
        $accessToken = $this->buildAccessToken(null, time() + 3600);
        $customer = $this->createMock(CustomerInterface::class);
        $store = $this->createMock(StoreInterface::class);

        $this->customerFactory->method('create')->willReturn($customer);
        $this->customerSession->method('getData')->willReturnMap([
            ['provider', false, 'facebook'],
            ['is_subscribed', false, '1'],
        ]);
        $customer->method('setFirstname')->willReturnSelf();
        $customer->method('setLastname')->willReturnSelf();
        $customer->method('setEmail')->willReturnSelf();
        $customer->method('setCustomAttribute')->willReturnSelf();
        $customer->method('getId')->willReturn(55);

        $this->randomPasswordGenerator->method('generate')->willReturn('RandPass123!');
        $this->accountManagement->method('getPasswordHash')->willReturn('hashed');
        $this->accountManagement->method('createAccount')->willReturn($customer);

        $store->method('getId')->willReturn(1);
        $this->storeManager->method('getStore')->willReturn($store);

        $this->subscriptionManager->expects($this->once())
            ->method('subscribeCustomer')
            ->with(55, 1);

        $this->subject->createAccount($user, $accessToken);
    }

    public function testCreateAccountThrowsWhenEmailMissing(): void
    {
        $user = $this->buildUser(null, 'No', 'Email', 'uid_999');
        $accessToken = $this->buildAccessToken(null, time() + 3600);
        $customer = $this->createMock(CustomerInterface::class);
        $this->customerFactory->method('create')->willReturn($customer);
        $this->customerSession->method('getData')->willReturn('google');

        $this->expectException(InputMismatchException::class);
        $this->subject->createAccount($user, $accessToken);
    }

    private function buildUser(?string $email, string $firstName, string $lastName, string $id): ResourceOwnerInterface&MockObject
    {
        $user = $this->createMock(ResourceOwnerInterface::class);
        $user->method('getEmail')->willReturn($email);
        $user->method('getFirstName')->willReturn($firstName);
        $user->method('getLastName')->willReturn($lastName);
        $user->method('getId')->willReturn($id);
        return $user;
    }

    private function buildAccessToken(?string $refreshToken, int $expires): AccessTokenInterface&MockObject
    {
        $token = $this->createMock(AccessTokenInterface::class);
        $token->method('getRefreshToken')->willReturn($refreshToken);
        $token->method('getExpires')->willReturn($expires);
        return $token;
    }
}
