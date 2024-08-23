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

namespace SinceLeo\Tenancy\Kernel\Http;

use Hyperf\Framework\Logger\StdoutLogger;
use Hyperf\Server\Listener\AfterWorkerStartListener;
use Psr\Container\ContainerInterface;

class WorkerStartListener extends AfterWorkerStartListener
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container->get(StdoutLogger::class));
    }
}
