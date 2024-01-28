<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider;

use BenJohnsonDev\SocialLogin\Model\Provider;
use BenJohnsonDev\SocialLogin\Model\ResourceModel\Provider as ProviderResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct(): void
    {
        $this->_init(Provider::class, ProviderResourceModel::class);
    }
}
