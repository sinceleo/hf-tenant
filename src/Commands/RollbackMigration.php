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

use Hyperf\Database\Commands\Migrations\RollbackCommand;
use Hyperf\Database\Migrations\Migrator;
use Nahuomall\HyperfTenancy\Concerns\HasATenantsOption;
use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Nahuomall\HyperfTenancy\Kernel\Tenancy;

class RollbackMigration extends RollbackCommand
{
    use HasATenantsOption;

    /**
     * The console command description.
     */
    protected string $description = 'Rollback migrations for tenant(s).';

    public function __construct(Migrator $migrator)
    {
        $this->migrator = $migrator;
        parent::__construct($migrator);
        parent::setName('tenants:rollback');
    }

    /**
     * @throws TenancyException
     * @throws \Throwable
     */
    public function handle(): void
    {
        Tenancy::runForMultiple($this->input->getOption('tenants'), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            if (! $this->confirmToProceed()) {
                return;
            }
            $this->migrator->setConnection(Tenancy::initDbConnectionName($tenant['id']));
            $this->migrator->setOutput($this->output)->rollback(
                $this->getMigrationPaths(),
                [
                    'pretend' => $this->input->getOption('pretend'),
                    'step' => (int) $this->input->getOption('step'),
                ]
            );
        });
    }
}
