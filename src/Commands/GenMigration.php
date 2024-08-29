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

namespace SinceLeo\Tenancy\Commands;

use Hyperf\Database\Commands\Migrations\GenMigrateCommand;
use SinceLeo\Tenancy\Kernel\Migrations\MigrationCreator;

class GenMigration extends GenMigrateCommand
{
    /**
     * The console command description.
     */
    protected string $description = 'Run migrations for tenant(s)';

    /**
     * Create a new migration install command instance.
     */
    public function __construct(MigrationCreator $creator)
    {
        parent::__construct($creator);
        parent::setName('tenants:migrate-gen');
    }
}
