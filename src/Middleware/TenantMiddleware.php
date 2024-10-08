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

namespace SinceLeo\Tenancy\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SinceLeo\Tenancy\Kernel\Exceptions\TenancyException;
use function Hyperf\Config\config;

class TenantMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws TenancyException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ignorePath = config('tenancy.ignore_path');

        $path = $request->getUri()->getPath();

        // 忽略的路径
        if (!empty($ignorePath) && in_array($path, $ignorePath)) {
            return $handler->handle($request);
        }

        tenancy()->init();

        return $handler->handle($request);
    }
}
