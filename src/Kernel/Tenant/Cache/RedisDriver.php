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
use Psr\Container\ContainerInterface;

class RedisDriver extends \Hyperf\Cache\Driver\RedisDriver
{
    /**
     * @throws TenancyException
     */
    public function __construct(ContainerInterface $container, array $config)
    {
        $config['prefix'] .= tenancy()->getId() . ':';
        parent::__construct($container, $config);
    }
}
