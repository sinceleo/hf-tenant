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

use Hyperf\Command\Command;
use SinceLeo\Tenancy\Concerns\HasATenantsOption;
use function Hyperf\Config\config;

class InitTenancy extends Command
{
    use HasATenantsOption;

    public function configure(): void
    {
        parent::configure();
        parent::setName('tenants:init');
        $this->setDescription('Tenancy system init command');
    }

    /**
     * Execute the console command.
     * @throws \Exception|\Throwable
     */
    public function handle(): void
    {
        $migrationPath = './migrations';

        $tenancyDb = config('databases.central');

        if (empty($tenancyDb)) {
            $this->error('Please configure the central database connection first.');
            return;
        }

        // 执行迁移
        $this->call('migrate', ['--path' => $migrationPath, '--database' => 'central']);

    }
}
