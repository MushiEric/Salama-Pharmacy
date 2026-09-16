<?php

test('a new tenant has sensible default settings', function () {
    $pharmacy = provisionPharmacy('settings-defaults');

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/tenant/settings')
        ->assertOk()
        ->assertJson([
            'data' => [
                'expiry_red_days' => 30,
                'expiry_yellow_days' => 90,
                'block_expired_sales' => true,
                'currency' => 'TZS',
                'timezone' => 'Africa/Dar_es_Salaam',
            ],
        ]);
});

test('pharmacy admin can override expiry and inventory settings', function () {
    $pharmacy = provisionPharmacy('settings-update');

    $this->actingAs($pharmacy['admin'])
        ->patchJson('/api/tenant/settings', [
            'expiry_red_days' => 14,
            'block_expired_sales' => false,
        ])
        ->assertOk()
        ->assertJsonPath('data.expiry_red_days', 14)
        ->assertJsonPath('data.block_expired_sales', false)
        ->assertJsonPath('data.expiry_yellow_days', 90);

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/tenant/settings')
        ->assertJsonPath('data.expiry_red_days', 14);
});

test('operational users without settings.manage permission cannot update tenant settings', function () {
    $pharmacy = provisionPharmacy('settings-forbidden');
    $cashier = operationalUser($pharmacy);

    $this->actingAs($cashier)
        ->patchJson('/api/tenant/settings', ['expiry_red_days' => 5])
        ->assertForbidden();
});
