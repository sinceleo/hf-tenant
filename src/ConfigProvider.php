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

namespace SinceLeo\Tenancy;

use Hyperf\Database\ConnectionResolverInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SinceLeo\Tenancy\Kernel\Event\EventDispatcherFactory;
use SinceLeo\Tenancy\Kernel\Tenant\ConnectionResolver;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                EventDispatcherInterface::class => EventDispatcherFactory::class,
                ConnectionResolverInterface::class => ConnectionResolver::class,
            ],
            'commands' => [
                ...[
                    Commands\MigrateMigration::class,
                    Commands\RollbackMigration::class,
                    Commands\ModelMigration::class,
                    Commands\GenMigration::class,
                    Commands\SeederMigration::class,
                    Commands\InitTenancy::class,
                ],
            ],
            'listeners' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'class_map' => [
                        Hyperf\Coroutine\Coroutine::class => BASE_PATH . '/Kernel/ClassMap/Coroutine.php',
                        Hyperf\Database\Migrations\Migration::class => BASE_PATH . '/Kernel/Migrations/Migration.php',
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
