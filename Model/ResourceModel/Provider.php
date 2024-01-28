<?php

declare(strict_types=1);

namespace BenJohnsonDev\SocialLogin\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Provider extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('social_login_providers', 'entity_id');
    }
}
