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

namespace Nahuomall\HyperfTenancy\Kernel\Resource\Src;

use Psr\Http\Message\ResponseInterface;

class AnonymousResourceCollection extends \Hyperf\Resource\Json\AnonymousResourceCollection
{
    public function __construct($resource, $collects)
    {
        parent::__construct($resource, $collects);
    }

    public function additional(array $data)
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    public function toResponse(): ResponseInterface
    {
        if ($this->isPaginatorResource($this->resource)) {
            return (new PaginatedResourceResponse($this))->toResponse();
        }

        return parent::toResponse();
    }

    protected function isPaginatorResource($resource): bool
    {
        return is_paginator($resource);
    }
}
