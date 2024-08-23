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

namespace SinceLeo\Tenancy\Kernel\Tenant;

use SinceLeo\Tenancy\Kernel\Exceptions\TenancyException;
use SinceLeo\Tenancy\Kernel\Tenancy;

trait CentralConnection
{
    /**
     * 获取连接名称.
     * @throws TenancyException
     */
    public function getConnectionName(): string
    {
        return Tenancy::getCentralConnection();
    }
}
