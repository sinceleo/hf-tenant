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

namespace SinceLeo\Tenancy\Kernel\Amqp\AsyncQueue;

use Hyperf\AsyncQueue\JobInterface;
use Hyperf\AsyncQueue\JobMessage;
use Hyperf\Contract\UnCompressInterface;

class AsyncMessage extends JobMessage
{
    public int|string $id;

    public function __construct(JobInterface $job)
    {
        parent::__construct($job);
        if (empty($this->id)) {
            $this->id = tenancy()->getId(false);
        }
    }

    public function __serialize(): array
    {
        return [
            $this->job,
            $this->attempts,
            $this->id,
        ];
    }

    public function __unserialize($serialized): void
    {
        [$job, $attempts, $id] = $serialized;
        if ($job instanceof UnCompressInterface) {
            $job = $job->uncompress();
        }
        $this->job = $job;
        $this->attempts = $attempts;
        $this->id = $id;
    }
}
