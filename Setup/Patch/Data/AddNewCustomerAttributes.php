<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes.
 */
class AddNewCustomerAttributes implements DataPatchInterface
{
    protected const SOCIAL_LOGIN_ATTRIBUTES = [
        'refresh_token' => [
            'type' => 'text',
            'label' => 'Refresh Token',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'sort_order' => 1000,
            'position' => 1000,
            'system' => 0,
        ],
        'provider' => [
            'type' => 'text',
            'label' => 'Provider',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'sort_order' => 1001,
            'position' => 1001,
            'system' => 0,
        ],
        'token_expire' => [
            'type' => 'varchar',
            'label' => 'Token Expire',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'sort_order' => 1002,
            'position' => 1002,
            'system' => 0,
        ],
        'social_uid' => [
            'type' => 'varchar',
            'label' => 'Social User ID',
            'input' => 'text',
            'required' => false,
            'visible' => false,
            'user_defined' => true,
            'sort_order' => 1002,
            'position' => 1002,
            'system' => 0,
        ],
    ];

    /**
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        protected CustomerSetupFactory $customerSetupFactory
    ) {
    }

    /**
     * Do Upgrade.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Validator\ValidateException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function apply()
    {
        // Don't disable the foreign key checks - remove startSetup() and endSetup()

        // create new customer attributes
        $customerSetup = $this->customerSetupFactory->create();

        foreach (self::SOCIAL_LOGIN_ATTRIBUTES as $attributeCode => $attributeData) {
            $customerSetup->addAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $attributeCode,
                $attributeData
            );
            $customerSetup->addAttributeToSet(
                Customer::ENTITY,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                $attributeCode
            );

        }
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * Example of implementation:
     *
     * [
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch1::class,
     *      \Vendor_Name\Module_Name\Setup\Patch\Patch2::class
     * ]
     *
     * @return string[]
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function getDependencies()
    {
        return [];
    }
}
