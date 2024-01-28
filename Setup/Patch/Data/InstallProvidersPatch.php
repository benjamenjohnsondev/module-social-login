<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Setup\Patch\Data;

use BenJohnsonDev\SocialLogin\Api\Data\ProviderInterfaceFactory;
use BenJohnsonDev\SocialLogin\Model\ProviderRepository;
use League\OAuth2\Client\Provider\Facebook;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Instagram;
use League\OAuth2\Client\Provider\LinkedIn;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes.
 */
class InstallProvidersPatch implements DataPatchInterface
{
    public const PROVIDERS_MATRIX = [
        'facebook' => [
            'label' => 'Facebook',
            'fields' => 'id,first_name,last_name,email',
            'icon' => 'fa-brands fa-facebook-f',
            'oauth_class' => Facebook::class,
            'default_config' => [
                'graphApiVersion' => 'v19.0',
            ],
        ],
        'github' => [
            'label' => 'GitHub',
            'icon' => 'fa-brands fa-github',
            'oauth_class' => Github::class,
        ],
        'google' => [
            'label' => 'Google',
            'icon' => 'fa-brands fa-google',
            'oauth_class' => Google::class,
        ],
        'instagram' => [
            'label' => 'Instagram',
            'icon' => 'fa-brands fa-instagram',
            'oauth_class' => Instagram::class,
        ],
        'linkedin' => [
            'label' => 'LinkedIn',
            'icon' => 'fa-brands fa-linkedin-in',
            'oauth_class' => LinkedIn::class,
        ],
    ];

    /**
     * @param \BenJohnsonDev\SocialLogin\Model\ProviderRepository $providerRepository
     * @param \BenJohnsonDev\SocialLogin\Api\Data\ProviderInterfaceFactory $providerFactory
     */
    public function __construct(
        protected ProviderRepository $providerRepository,
        protected ProviderInterfaceFactory $providerFactory,
    ) {
    }

    /**
     * Do Upgrade.
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function apply()
    {
        // Don't disable the foreign key checks - remove startSetup() and endSetup()
        foreach (self::PROVIDERS_MATRIX as $providerCode => $providerData) {
            $provider = $this->providerFactory->create();
            $provider->setData($providerData);
            $provider->setCode($providerCode);

            if ($provider['default_config'] !== null) {
                $provider->setDefaultConfig($provider['default_config']);
            }

            $this->providerRepository->save($provider);
        }
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
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
     */
    public static function getDependencies()
    {
        return [];
    }
}
