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

namespace SinceLeo\Tenancy\Kernel\Migrations;

use SinceLeo\Tenancy\Kernel\Tenancy;

abstract class TenancyMigration
{
    /**
     * Enables, if supported, wrapping the migration within a transaction.
     */
    public bool $withinTransaction = true;

    /**
     * The name of the database connection to use.
     */
    protected string $connection = 'default';

    /**
     * Get the migration connection name.
     */
    public function getConnection(): string
    {
        return Tenancy::tenancyDatabase();
    }
}
