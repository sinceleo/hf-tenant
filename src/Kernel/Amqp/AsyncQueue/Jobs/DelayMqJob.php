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

namespace Nahuomall\HyperfTenancy\Kernel\Amqp\AsyncQueue\Jobs;

use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Producer;
use Hyperf\AsyncQueue\Job;
use Hyperf\Context\ApplicationContext;
use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 延迟队列
 * Class DelayMqJob.
 */
class DelayMqJob extends Job
{
    public mixed $params;

    public string $producerClassName;

    /**
     * 任务执行失败后的重试次数，即最大执行次数为 $maxAttempts+1 次
     */
    protected int $maxAttempts = 2;

    /**
     * @throws \Exception
     */
    public function __construct(string $producerClassName, ...$params)
    {
        if (! class_exists($producerClassName)) {
            throw new TenancyException(sprintf('%s class no exist', $producerClassName));
        }
        $producerClass = new $producerClassName(...$params);
        if (! $producerClass instanceof ProducerMessage) {
            throw new TenancyException(sprintf('%s class example not ProducerMessage', $producerClassName));
        }
        $this->params = $params;
        $this->producerClassName = $producerClassName;
    }

    /**
     * 设置重试次数.
     * @param int $maxAttempts
     */
    public function setMaxAttempts(int $maxAttempts): static
    {
        $this->maxAttempts = $maxAttempts;
        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(): void
    {
        $className = $this->producerClassName;
        $class = new $className(...$this->params);
        if (function_exists('newQueue')) {
            newQueue($class);
        } else {
            $producer = ApplicationContext::getContainer()->get(Producer::class);
            $producer->produce($class);
        }
    }
}
