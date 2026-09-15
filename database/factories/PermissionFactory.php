<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::lower(fake()->unique()->lexify('custom.??????')),
            'description' => fake()->sentence(),
        ];
    }
}
