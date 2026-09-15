<?php

namespace App\Modules\Tenancy\Infrastructure\Persistence;

use App\Modules\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TenantScopedBinding
{
    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @return TModel
     */
    public static function resolve(string $model, string $id): Model
    {
        $user = Auth::user();

        if (! $user instanceof User || $user->tenant_id === null) {
            throw new NotFoundHttpException;
        }

        $record = $model::withoutGlobalScopes()
            ->whereKey($id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (! $record instanceof $model) {
            throw new NotFoundHttpException;
        }

        return $record;
    }
}
