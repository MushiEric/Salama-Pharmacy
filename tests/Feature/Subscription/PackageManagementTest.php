<?php

use App\Modules\Subscription\Application\SubscriptionEntitlementService;
use App\Modules\Subscription\Application\TenantSubscriptionManagementService;
use App\Modules\Subscription\Models\Package;
use App\Modules\Tenancy\Application\TenantContext;

test('platform superadmin can list seeded packages', function () {
    $superadmin = platformSuperadmin();

    $this->actingAs($superadmin)
        ->getJson('/api/platform/packages')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('platform superadmin can create a package', function () {
    $superadmin = platformSuperadmin();

    $this->actingAs($superadmin)
        ->postJson('/api/platform/packages', [
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'max_branches' => null,
            'max_users' => null,
            'features' => ['advanced_reports'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'enterprise');
});

test('tenant users cannot manage packages', function () {
    $pharmacy = provisionPharmacy('no-package-access');

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/platform/packages')
        ->assertForbidden();
});

test('platform superadmin can view and change a tenant subscription', function () {
    $superadmin = platformSuperadmin();
    $pharmacy = provisionPharmacy('change-sub');
    $premium = Package::query()->where('slug', 'premium')->firstOrFail();

    $this->actingAs($superadmin)
        ->getJson("/api/platform/tenants/{$pharmacy['tenant']->id}/subscription")
        ->assertOk()
        ->assertJsonPath('data.package.slug', 'standard');

    $this->actingAs($superadmin)
        ->patchJson("/api/platform/tenants/{$pharmacy['tenant']->id}/subscription", [
            'package_id' => $premium->id,
            'status' => 'active',
        ])
        ->assertOk()
        ->assertJsonPath('data.package.slug', 'premium')
        ->assertJsonPath('data.status', 'active');
});

test('canUse reflects the current package feature list', function () {
    $pharmacy = provisionPharmacy('feature-flag', 'basic');
    app(TenantContext::class)->setTenant($pharmacy['tenant']);

    expect((new SubscriptionEntitlementService(app(TenantContext::class)))->canUse('advanced_reports'))
        ->toBeFalse();

    $standard = Package::query()->where('slug', 'standard')->firstOrFail();
    app(TenantSubscriptionManagementService::class)->assign($pharmacy['tenant'], ['package_id' => $standard->id]);

    expect((new SubscriptionEntitlementService(app(TenantContext::class)))->canUse('advanced_reports'))
        ->toBeTrue();
});

test('tenant self-service subscription endpoint returns only its own subscription', function () {
    $pharmacy = provisionPharmacy('self-service-sub');

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/subscription')
        ->assertOk()
        ->assertJsonPath('data.tenant_id', $pharmacy['tenant']->id)
        ->assertJsonPath('data.package.slug', 'standard');
});
