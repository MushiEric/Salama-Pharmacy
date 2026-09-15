<?php

use App\Modules\Subscription\Application\TenantSubscriptionManagementService;
use App\Modules\Subscription\Models\Package;

test('branch creation is blocked once the package branch limit is reached', function () {
    $pharmacy = provisionPharmacy('branch-limit', 'basic');

    // The Basic package allows 1 branch, already used by the onboarding branch.
    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', ['name' => 'Second Branch'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('limit');
});

test('user creation is blocked once the package user limit is reached', function () {
    $pharmacy = provisionPharmacy('user-limit');
    $tightPackage = Package::factory()->create(['max_users' => 1]);

    app(TenantSubscriptionManagementService::class)->assign($pharmacy['tenant'], [
        'package_id' => $tightPackage->id,
    ]);

    // The onboarding admin already occupies the single available user slot.
    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/users', [
            'name' => 'Extra Cashier',
            'email' => 'extra@pharmacy.test',
            'password' => 'password123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('limit');
});

test('a package with no limit configured allows unlimited branches', function () {
    $pharmacy = provisionPharmacy('unlimited-branches', 'premium');

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', ['name' => 'Second Branch'])
        ->assertCreated();
});
