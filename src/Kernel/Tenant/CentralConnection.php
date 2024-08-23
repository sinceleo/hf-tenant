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

namespace Nahuomall\HyperfTenancy\Kernel\Tenant;

use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Nahuomall\HyperfTenancy\Kernel\Tenancy;

trait CentralConnection
{
    /**
     * 获取连接名称.
     * @return string
     * @throws TenancyException
     */
    public function getConnectionName(): string
    {
        return Tenancy::getCentralConnection();
    }
}
