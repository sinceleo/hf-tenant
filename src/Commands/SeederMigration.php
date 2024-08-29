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

use Hyperf\Database\Commands\Seeders\SeedCommand;
use Hyperf\Database\Seeders\Seed;
use SinceLeo\Tenancy\Concerns\HasATenantsOption;
use SinceLeo\Tenancy\Kernel\Tenancy;

class SeederMigration extends SeedCommand
{
    use HasATenantsOption;

    /**
     * The console command description.
     */
    protected string $description = 'Run seeder for tenant(s)';

    public function __construct(Seed $seed)
    {
        $this->seed = $seed;
        parent::__construct($seed);
        parent::setName('tenants:seeder');
    }

    /**
     * Execute the console command.
     * @throws \Exception|\Throwable
     */
    public function handle(): void
    {
        Tenancy::runForMultiple($this->input->getOption('tenants'), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");

            if (! $this->confirmToProceed()) {
                return;
            }
            $this->input->setOption('database', Tenancy::initDbConnectionName($tenant['id']));
            parent::handle();
        });
    }
}
