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

namespace Nahuomall\HyperfTenancy\Kernel\Tenant;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Support\Traits\StaticInstance;
use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Nahuomall\HyperfTenancy\Kernel\Tenancy;
use Nahuomall\HyperfTenancy\Kernel\Tenant\Models\Tenants as TenantModel;

class Tenant
{
    use StaticInstance;

    protected ?TenantModel $tenant;

    /**
     * 初始化租户.
     *
     * @param string $id 租户标识，可选参数。如果不提供，将尝试从HTTP请求中获取。
     * @param bool $isCheck 是否检查租户有效性。如果设置为true，将阻止无效租户的初始化。
     *
     * @return null|TenantModel 成功初始化后返回租户模型实例，否则返回null
     * @throws TenancyException
     */
    public function init(string $id = '', bool $isCheck = true): ?TenantModel
    {
        // 当没有提供$id且需要检查租户有效性时，尝试从HTTP请求中获取租户ID
        if ($id === '' && $isCheck && Tenancy::checkIfHttpRequest()) {
            $request = ApplicationContext::getContainer()->get(RequestInterface::class);
            $id = $request->getHeaderLine('x-tenant-id') ?? $request->query('tenant');
            // 如果从请求中未能获取到ID，并且请求中包含了域名，则尝试通过域名获取租户ID
            if ($id === '') {
                $id = Tenancy::domainModel()::tenantIdByDomain($request->header('Host'));
            }
        }

        // 过滤根目录
        if ($id === '' && $isCheck) {
            throw new TenancyException('The tenant ID is missing or invalid.');
        }

        $tenant = tenancy()->getTenant();

        // 如果当前上下文中没有租户信息，或者租户ID与提供的ID不匹配，则尝试获取新的租户信息
        if (! $tenant || $tenant->id !== $id) {
            try {
                /** @var TenantModel $tenant */
                $tenant = Tenancy::tenantModel()::tenantsAll($id);
            } catch (\Exception $exception) {
                // 如果捕获到的是TenancyException，并且$isCheck为true，则销毁当前租户并抛出异常
                if ($exception instanceof TenancyException && $isCheck) {
                    $this->destroy();
                    throw $exception;
                }
                // 如果不是TenancyException，直接抛出
                throw $exception;
            }
        }

        // 设置租户到上下文中
        Context::set(Tenancy::getContextKey(), $tenant);
        return $tenant;
    }

    /**
     * 销毁租户.
     */
    public function destroy(): void
    {
        Context::set(Tenancy::getContextKey(), null);
    }

    /**
     * 获取租户.
     */
    public function getTenant(): ?TenantModel
    {
        $tenant = Context::get(Tenancy::getContextKey());
        if (empty($tenant)) {
            return null;
        }
        return $tenant;
    }

    /**
     * @throws TenancyException
     */
    public function getId(bool $isCheck = true): ?string
    {
        $tenant = $this->getTenant();
        // 过滤根目录
        if (empty($tenant) && $isCheck) {
            throw new TenancyException('The tenant is invalid.');
        }
        return $tenant->id ?? null;
    }

    /**
     * 指定租户内执行.
     * @throws \Exception
     */
    public function runForMultiple($tenants, callable $callable): void
    {
        Tenancy::runForMultiple($tenants, $callable);
    }
}
