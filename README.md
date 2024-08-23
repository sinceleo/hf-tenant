
<p align="center">
    <a href="https://tenancyforlaravel.com"><img width="800" src="/art/logo.png" alt="Tenancy for Laravel logo" /></a>
</p>

# Tenancy for Hyperf

## 介绍

> 一个轻量级租户系统，支持多租户，支持跨租户请求，支持跨租户队列，支持跨租户数据库，支持跨租户缓存，支持跨租户日志，支持跨租户事件，支持跨租户路由，支持跨租户服务，支持跨租户配置，支持跨租户中间件，支持跨租户控制器，支持跨租户模型，支持跨租户服务提供者，

> 基于：Laravel Tenancy为灵感开发的一套简易版租户插件，让Hyperf也可以支持多租户，像Laravel Tenancy一样开发单租户业务一样，通过组件实现多租户的模式，做到真正的数据隔离
> 实现方式：通过中间件实现租户编号的获取，并设置到协程上下文中，通过重写数据库连接，在请求数据库时，自动切换到对应租户的数据库连接

## 安装

```shell
  composer require sinceleo/hyperf-tenancy
```

# 配置部分


## 生成配置文件

```
php artisan vendor:publish sinceleo/hyperf-tenancy
```

## 修改缓存配置

- cache.php修改为如下配置

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
        'driver' => \SinceLeo\Tenancy\Kernel\Tenant\Cache\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'tenant:cache:'
    ],
];

```

- databases.php 修改为如下配置

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

- redis.php 配置修改为如下配置

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

    获取租户编号的方式有两种：
    
    一、前端接口请求时在header中携带租户编号，如： X-TENANT-ID:xxxx

    二、在请求时通过域名获取租户编号，如：http://baidu.domain.com 内部将通过域名获取租户编号
    
    
- 注：租户编号请在central中央库中进行维护，通过域名识别租户编号需要在central库中的tenant表与domain_tenants表中存在相应配置


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

