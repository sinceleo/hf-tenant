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

use Hyperf\Database\ConnectionInterface;
use SinceLeo\Tenancy\Kernel\Exceptions\TenancyException;
use SinceLeo\Tenancy\Kernel\Tenancy;

class ConnectionResolver extends \Hyperf\DbConnection\ConnectionResolver
{
    /**
     * All the registered connections.
     */
    protected array $connections = [];

    /**
     * Get a database connection instance.
     *
     * @param null $name
     * @throws TenancyException
     */
    public function connection($name = null): ConnectionInterface
    {
        if (! empty(tenancy()->getId(false)) && ! in_array($name, Tenancy::extendConnections())) {
            $name = Tenancy::initDbConnectionName();
        }
        return parent::connection($name);
    }
}
