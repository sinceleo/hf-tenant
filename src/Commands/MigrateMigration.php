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

namespace Nahuomall\HyperfTenancy\Commands;

use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Hyperf\Database\Migrations\Migrator;
use Nahuomall\HyperfTenancy\Concerns\HasATenantsOption;
use Nahuomall\HyperfTenancy\Kernel\Tenancy;

class MigrateMigration extends MigrateCommand
{
    use HasATenantsOption;

    /**
     * The console command description.
     */
    protected string $description = 'Run migrations for tenant(s)';

    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
        parent::__construct($migrator);
        parent::setName('tenants:migrate');
    }

    /**
     * Execute the console command.
     * @return void
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
