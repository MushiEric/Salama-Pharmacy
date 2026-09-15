<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\Permission;
use App\Modules\Identity\Presentation\Http\Resources\PermissionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PermissionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PermissionResource::collection(
            Permission::query()->orderBy('code')->get()
        );
    }
}
