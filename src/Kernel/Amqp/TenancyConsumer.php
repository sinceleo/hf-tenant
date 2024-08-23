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

use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Packer\Packer;
use Hyperf\Context\ApplicationContext;
use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class TenancyConsumer extends ConsumerMessage
{
    /**
     * 消息体反序列化.
     * @param string $data
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TenancyException
     */
    public function unserialize(string $data): mixed
    {
        $container = ApplicationContext::getContainer();
        $packer = $container->get(Packer::class);
        $result = $packer->unpack($data);
        $body = json_decode($result, true);
        ['payload' => $payload, 'tenant_id' => $tenantId] = $body;
        if (! empty($tenantId)) {
            tenancy()->init($tenantId);
        }
        return $payload;
    }
}
