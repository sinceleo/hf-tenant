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

use Carbon\Carbon;
use Hyperf\Collection\Collection;
use Hyperf\Context\Context;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use SinceLeo\Tenancy\Kernel\Exceptions\TenancyException;
use SinceLeo\Tenancy\Kernel\Tenant\CentralConnection;

/**
 * @property string $id
 * @property string $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Tenants extends Model
{
    use SoftDeletes;
    use CentralConnection;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'tenants';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'data', 'created_at', 'updated_at'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'id' => 'string',
        'data' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function tenantsAll(?string $id = null, bool $reset = false)
    {
        $tenants = Context::get(self::class);
        if (empty($tenants) || $reset) {
            $tenants = self::query()->orderBy('created_at')->get();
            Context::set(self::class, $tenants);
        }
        if (! empty($id)) {
            $tenant = Collection::make($tenants)->where('id', $id)->first();
            if (empty($tenant)) {
                if ($reset) {
                    throw new TenancyException(
                        sprintf('The tenant %s is invalid', $id)
                    );
                }
                return self::tenantsAll($id, true);
            }
            return $tenant;
        }
        return $tenants;
    }
}
