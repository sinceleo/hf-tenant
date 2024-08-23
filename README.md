# hyperf-tenancy 安装使用
```shell
composer require cmslz/hyperf-tenancy
```

https://github.com/cmslz/hyperf-tenancy

## 介绍

> 1. 可通过header参数或在GET参数绑定对应租户
>> Header:x-tenant-id:xxxx
> > GET:tenant = xxx

## 配置

- amqp.php

> \Nahuomall\HyperfTenancy\Kernel\Tenant\AsyncQueue\RedisDriver::class

- annotations.php

```PHP
return [
    'scan' => [
    'class_map' => [
            Hyperf\Coroutine\Coroutine::class => BASE_PATH . '/vendor/cmslz/hyperf-tenancy/src/Kernel/ClassMap/Coroutine.php',
            Hyperf\Database\Migrations\Migration::class => BASE_PATH . '/vendor/cmslz/hyperf-tenancy/src/Kernel/Migrations/Migration.php',
        ]
    ]
];
```

- dependencies.php

```PHP
return [
    Psr\EventDispatcher\EventDispatcherInterface::class => \Nahuomall\HyperfTenancy\Kernel\Event\EventDispatcherFactory::class,
    Hyperf\Database\ConnectionResolverInterface::class => \Nahuomall\HyperfTenancy\Kernel\Tenant\ConnectionResolver::class,
];
```

- tenancy.php

> [config.tenancy](/publish/config.php)

- cache.php

```PHP
return [
    ...[],
    // 中央域缓存
    'default' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'c:',
    ],
    // 租户缓存
    'tenant' => [
        'driver' => \Nahuomall\HyperfTenancy\Kernel\Tenant\Cache\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'tenant:cache:'
    ],
];

```

- databases.php

```PHP
return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', ''),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_general_ci'),
        'prefix' => env('DB_CENTRAL_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 100,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
        ],
        'cache' => [
            'handler' => Hyperf\ModelCache\Handler\RedisHandler::class,
            'cache_key' => '{mc:%s:m:%s}:%s:%s',
            'prefix' => 'central',
            'ttl' => 3600 * 24,
            'empty_model_ttl' => 600,
            'load_script' => true,
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'App\Kernel\Base\BaseModel',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
            ],
        ],
    ],
    'central' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'central'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_general_ci'),
        'prefix' => env('DB_CENTRAL_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 100,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
        ],
        'cache' => [
            'handler' => Hyperf\ModelCache\Handler\RedisHandler::class,
            'cache_key' => '{mc:%s:m:%s}:%s:%s',
            'prefix' => 'central',
            'ttl' => 3600 * 24,
            'empty_model_ttl' => 600,
            'load_script' => true,
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'App\Kernel\Base\BaseModel',
                'force_casts' => true,
                'inheritance' => 'Model',
                'uses' => '',
                'refresh_fillable' => true,
                'table_mapping' => [],
            ],
        ],
    ],
];
```

- redis.php

```PHP
return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int)env('REDIS_PORT', 6379),
        'db' => (int)env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('REDIS_MAX_IDLE_TIME', 60),
        ],
    ],
    // 租户通用redis
    'tenant' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int)env('REDIS_PORT', 6379),
        'db' => (int)env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('REDIS_MAX_IDLE_TIME', 60),
        ]
    ]
];
```

## 使用

    在需要使用路由添加中间件 `TenantMiddleware::class`

### 队列使用

#### 消费端

```PHP
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Job;

use Hyperf\AsyncQueue\Job;

class TenantJob extends Job
{
    protected $params;
    public function __construct($params)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->params = $params;
    }

    public function handle()
    {
        var_dump($this->params, tenancy()->getId());
    }
}
```

#### 客户端

```PHP
use App\Job\TenantJob;
queue_push(new TenantJob(['2131313']),5);
```

