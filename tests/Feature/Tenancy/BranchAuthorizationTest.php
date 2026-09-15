<?php

use App\Modules\Tenancy\Domain\Enums\BranchStatus;
use App\Modules\Tenancy\Models\Branch;

test('pharmacy admin may operate tenant-wide without a branch header', function () {
    $pharmacy = provisionPharmacy('admin-branch');

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.branch_id', null)
        ->assertJsonPath('data.is_pharmacy_admin', true);
});

test('pharmacy admin may select a branch in their tenant', function () {
    $pharmacy = provisionPharmacy('select-branch');

    $this->actingAs($pharmacy['admin'])
        ->withHeaders(['X-Branch-Id' => $pharmacy['branch']->id])
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.branch_id', $pharmacy['branch']->id);
});

test('operational users cannot switch to another branch', function () {
    $pharmacy = provisionPharmacy('locked-branch');
    $otherBranch = Branch::factory()->create([
        'tenant_id' => $pharmacy['tenant']->id,
        'name' => 'Second',
        'status' => BranchStatus::Active,
    ]);
    $cashier = operationalUser($pharmacy);

    $this->actingAs($cashier)
        ->withHeaders(['X-Branch-Id' => $otherBranch->id])
        ->getJson('/api/user')
        ->assertForbidden();
});

test('branch create is scoped to the authenticated tenant', function () {
    $pharmacy = provisionPharmacy('create-branch');

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/branches', [
            'name' => 'Uptown',
            'tenant_id' => 'should-be-ignored',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Uptown')
        ->assertJsonPath('data.tenant_id', $pharmacy['tenant']->id);
});
