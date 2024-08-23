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

namespace Nahuomall\HyperfTenancy;

use Hyperf\Database\ConnectionResolverInterface;
use Nahuomall\HyperfTenancy\Kernel\Tenant\ConnectionResolver;

class TenancyConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ConnectionResolverInterface::class => ConnectionResolver::class,
            ],
            'commands' => [
                ...[
                    Commands\MigrateMigration::class,
                    Commands\RollbackMigration::class,
                    Commands\ModelMigration::class,
                    Commands\GenMigration::class,
                ],
            ],
            'listeners' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 组件默认配置文件，即执行命令后会把 source 的对应的文件复制为 destination 对应的的文件
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'description of this config file.', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/../publish/config.php',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/config/autoload/tenancy.php', // 复制为这个路径下的该文件
                ],
            ],
        ];
    }
}
