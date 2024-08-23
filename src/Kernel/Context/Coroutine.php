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

namespace SinceLeo\Tenancy\Kernel\Context;

use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use SinceLeo\Tenancy\Kernel\Exceptions\TenancyException;
use SinceLeo\Tenancy\Kernel\Log\AppendRequestIdProcessor;

class Coroutine
{
    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     * @throws TenancyException
     */
    public function create(callable $callable): int
    {
        $id = \Hyperf\Coroutine\Coroutine::id();
        $tenantId = tenancy()->getId(false);
        $coroutine = Co::create(
            function () use ($callable, $id, $tenantId) {
                try {
                    // Shouldn't copy all contexts to avoid socket already been bound to another coroutine.
                    Context::copy(
                        $id,
                        [
                            AppendRequestIdProcessor::REQUEST_ID,
                            ServerRequestInterface::class,
                        ]
                    );
                    if ($tenantId) {
                        tenancy()->init($tenantId);
                    }
                    call($callable);
                } catch (\Throwable $throwable) {
                    $this->logger->warning((string) $throwable);
                }
            }
        );

        try {
            return $coroutine->getId();
        } catch (\Throwable $throwable) {
            $this->logger->warning((string) $throwable);
            return -1;
        }
    }
}
