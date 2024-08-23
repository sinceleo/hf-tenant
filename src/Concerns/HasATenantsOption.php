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

namespace Nahuomall\HyperfTenancy\Concerns;

use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Nahuomall\HyperfTenancy\Kernel\Tenancy;
use Symfony\Component\Console\Input\InputOption;

trait HasATenantsOption
{
    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        array_push($options, ['tenants', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '', null]);
        return $options;
    }

    /**
     * Get the tenants.
     * @return mixed
     * @throws TenancyException
     */
    protected function getTenants(): mixed
    {
        return Tenancy::tenantModel()::query()
            ->when($this->input->getOption('tenants'), function ($query) {
                $query->whereIn(Tenancy::tenantModel()->primaryKey, $this->input->getOption('tenants'));
            })
            ->cursor();
    }
}
