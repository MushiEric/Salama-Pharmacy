<?php

use App\Modules\Subscription\Domain\Enums\SubscriptionStatus;

test('an expired subscription blocks writes but still allows reads', function () {
    $pharmacy = provisionPharmacy('expired-sub');
    $pharmacy['subscription']->update(['status' => SubscriptionStatus::Expired]);

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/branches')
        ->assertOk();

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', ['name' => 'New Branch'])
        ->assertForbidden();
});

test('a suspended subscription blocks writes', function () {
    $pharmacy = provisionPharmacy('suspended-sub');
    $pharmacy['subscription']->update(['status' => SubscriptionStatus::Suspended]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/users', [
            'name' => 'New Cashier',
            'email' => 'new-cashier@pharmacy.test',
            'password' => 'password123',
        ])
        ->assertForbidden();
});

test('a cancelled subscription blocks writes', function () {
    $pharmacy = provisionPharmacy('cancelled-sub');
    $pharmacy['subscription']->update(['status' => SubscriptionStatus::Cancelled]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', ['name' => 'New Branch'])
        ->assertForbidden();
});

test('a trial subscription allows writes', function () {
    $pharmacy = provisionPharmacy('trial-sub');

    expect($pharmacy['subscription']->status)->toBe(SubscriptionStatus::Trial);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', ['name' => 'New Branch'])
        ->assertCreated();
});

test('an active subscription allows writes', function () {
    $pharmacy = provisionPharmacy('active-sub');
    $pharmacy['subscription']->update(['status' => SubscriptionStatus::Active]);

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', ['name' => 'New Branch'])
        ->assertCreated();
});

test('a tenant with no subscription is read-only by default', function () {
    $pharmacy = provisionPharmacy('no-sub');
    $pharmacy['subscription']->delete();

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', ['name' => 'New Branch'])
        ->assertForbidden();
});

test('platform superadmin actions are not gated by tenant subscription state', function () {
    $superadmin = platformSuperadmin();
    $pharmacy = provisionPharmacy('platform-writes');
    $pharmacy['subscription']->update(['status' => SubscriptionStatus::Expired]);

    $this->actingAs($superadmin)
        ->patchJson("/api/platform/tenants/{$pharmacy['tenant']->id}/subscription", [
            'status' => 'active',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'active');
});
