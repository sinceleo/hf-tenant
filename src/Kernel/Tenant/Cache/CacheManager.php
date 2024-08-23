<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Nahuomall\HyperfTenancy\Kernel\Tenant\Cache;

use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;

class CacheManager extends \Hyperf\Cache\CacheManager
{
    /**
     * @throws TenancyException
     */
    public function setTenantConfig($configKey): static
    {
        if (! $this->config->has($configKey)) {
            $config = $this->config->get('cache.' . $this->config->get('tenancy.cache.tenant_connection', 'tenant'));
            // 每个tenant后缀不一样
            $config['prefix'] .= tenancy()->getId() . ':';
            $this->config->set('cache.' . $configKey, $config);
        }
        return $this;
    }
}
