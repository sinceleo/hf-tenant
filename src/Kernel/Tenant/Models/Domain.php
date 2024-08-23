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

namespace Nahuomall\HyperfTenancy\Kernel\Tenant\Models;

use Hyperf\Collection\Collection;
use Hyperf\Context\Context;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Nahuomall\HyperfTenancy\Kernel\Tenant\CentralConnection;

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
     * @return string
     *                Created by since at 2023/2/16 17:46
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

    public static function tenantIdByDomain(string $domain)
    {
        return (string) self::domainsAll($domain)->tenant_id;
    }

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
