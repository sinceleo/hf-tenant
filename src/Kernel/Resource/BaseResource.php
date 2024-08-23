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

namespace SinceLeo\Tenancy\Kernel\Resource;

use Hyperf\Resource\Json\JsonResource;
use SinceLeo\Tenancy\Kernel\Resource\Src\AnonymousResourceCollection;

class BaseResource extends JsonResource
{
    public static function collection($resource)
    {
        if (! is_paginator($resource)) {
            return parent::collection($resource);
        }
        return tap(
            new AnonymousResourceCollection($resource, static::class),
            function ($collection) {
                $collection->preserveKeys = (new static([]))->preserveKeys;
            }
        );
    }
}
