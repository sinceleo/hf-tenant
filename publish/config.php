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
use SinceLeo\Tenancy\Kernel\Tenant\Models\Domain;
use SinceLeo\Tenancy\Kernel\Tenant\Models\Tenants;

return [
    'tenant_model' => Tenants::class,
    'domain_model' => Domain::class,
    // 租户上下文
    'context' => 'tenant_context',
    'central_domains' => [
        '127.0.0.1',
        'localhost',
    ],
    // 忽略的路由
    'ignore_path' => [],
    'database' => [
        // 不允许为default
        'central_connection' => env('TENANCY_CENTRAL_CONNECTION', 'central'),
        // 扩展链接
        'extend_connections' => explode(',', env('TENANCY_EXTEND_CONNECTIONS', '')),
        // 租户数据库前缀
        'tenant_prefix' => 'tenant_',
        // 租户数据库表前缀
        'tenant_table_prefix' => '',
        'base_database' => 'base',
    ],
    'cache' => [
        'tenant_prefix' => 'tenant_',
        'tenant_connection' => 'tenant',
        'central_connection' => 'central',
    ],
];
