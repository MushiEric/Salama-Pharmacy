<?php

namespace App\Modules\Identity\Infrastructure\Auth;

use App\Modules\Tenancy\Infrastructure\Persistence\TenantScope;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantAwareUserProvider extends EloquentUserProvider
{
    /**
     * @param  Model|null  $model
     * @return Builder<Model>
     */
    protected function newModelQuery($model = null)
    {
        return parent::newModelQuery($model)->withoutGlobalScope(TenantScope::class);
    }
}
