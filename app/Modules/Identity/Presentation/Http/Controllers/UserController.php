<?php

namespace App\Modules\Identity\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Application\Services\UserManagementService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Presentation\Http\Requests\StoreUserRequest;
use App\Modules\Identity\Presentation\Http\Requests\UpdateUserRequest;
use App\Modules\Identity\Presentation\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(
            User::query()->with('roles')->orderBy('name')->paginate()
        );
    }

    public function store(StoreUserRequest $request, UserManagementService $users): JsonResponse
    {
        $user = $users->create($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->load('roles'));
    }

    public function update(UpdateUserRequest $request, User $user, UserManagementService $users): UserResource
    {
        return new UserResource($users->update($user, $request->validated()));
    }
}
