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

namespace SinceLeo\Tenancy\Kernel\Tenant\Models;

use Hyperf\Collection\Collection;
use Hyperf\Context\Context;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use SinceLeo\Tenancy\Kernel\Exceptions\TenancyException;
use SinceLeo\Tenancy\Kernel\Tenant\CentralConnection;

/**
 * Class Domain.
 * @property int $id 自增id
 * @property string $domain 租户域名
 * @property string $tenant_id 关联租户id
 * @property null|string $createdAt 创建时间
 * @property null|string $updatedAt 更新时间
 * @property null|string $deletedAt 删除时间
 */
class Domain extends Model
{
    use SoftDeletes;
    use CentralConnection;

    protected ?string $table = 'tenant_domains';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'domain', 'tenant_id', 'created_at', 'updated_at'];

    /**
     * 获取租户域名.
     * @param string $tenantId
     * @return string
     */
    public static function domainByTenantId(string $tenantId): string
    {
        $domain = self::query()->where('tenant_id', $tenantId)->value('domain');
        if (empty($domain)) {
            return '';
        }

        $scheme = config('app_env') === 'local' ? 'http://' : 'https://';
        return $scheme . $domain;
    }

    /**
     * 获取租户id.
     * @param string $domain 域名
     * @return string
     * @throws TenancyException
     */
    public static function tenantIdByDomain(string $domain): string
    {
        return (string) self::domainsAll($domain)->tenant_id;
    }

    /**
     * 获取所有域名.
     * @param null|string $domain 域名
     * @param bool $reset 是否重置
     * @return Collection
     * @throws TenancyException
     */
    public static function domainsAll(?string $domain = null, bool $reset = false)
    {
        $domains = Context::get(self::class);
        if (empty($domains) || $reset) {
            $domains = self::query()->get();
            Context::set(self::class, $domains);
        }
        if (! empty($domain)) {
            $domainInfo = Collection::make($domains)->where('domain', $domain)->first();
            if (empty($domainInfo)) {
                if ($reset) {
                    throw new TenancyException('The domain is invalid.');
                }
                return self::domainsAll($domain, true);
            }
            return $domainInfo;
        }
        return $domains;
    }
}
