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

namespace Nahuomall\HyperfTenancy\Kernel\Amqp;

use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Packer\Packer;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Context\ApplicationContext;
use Nahuomall\HyperfTenancy\Kernel\Amqp\AsyncQueue\Jobs\DelayMqJob;
use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class TenancyProducer extends ProducerMessage
{
    /**
     * 序列化.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TenancyException
     */
    public function serialize(): string
    {
        $packer = ApplicationContext::getContainer()->get(Packer::class);
        $this->payload = json_encode(['payload' => $this->payload, 'tenant_id' => tenancy()->getId(false)]);
        return $packer->pack($this->payload);
    }

    /**
     * 设置延迟时间.
     * @param int $maxAttempts // 设置重试次数
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function delay(int $delay, int $maxAttempts = 0, string $key = 'default'): bool
    {
        $driver = di()->get(DriverFactory::class)->get($key);
        $job = new DelayMqJob(static::class, $this->payload);
        if (! empty($maxAttempts)) {
            $job->setMaxAttempts($maxAttempts);
        }
        return $driver->push($job, $delay);
    }
}
