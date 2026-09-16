<?php

use App\Modules\DrugCatalog\Models\GenericDrug;
use App\Modules\DrugCatalog\Models\MasterDrug;

test('platform superadmin can create a generic drug and a master drug under it', function () {
    $superadmin = platformSuperadmin();

    $genericResponse = $this->actingAs($superadmin)
        ->postJson('/api/platform/generic-drugs', ['name' => 'Paracetamol'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Paracetamol');

    $genericId = $genericResponse->json('data.id');

    $this->actingAs($superadmin)
        ->postJson('/api/platform/master-drugs', [
            'generic_drug_id' => $genericId,
            'brand_name' => 'Panadol',
            'dosage_form' => 'Tablet',
            'strength' => '500mg',
        ])
        ->assertCreated()
        ->assertJsonPath('data.brand_name', 'Panadol')
        ->assertJsonPath('data.generic_drug_name', 'Paracetamol');
});

test('tenant users cannot manage the master catalog but can browse it', function () {
    $pharmacy = provisionPharmacy('catalog-browse');
    $masterDrug = MasterDrug::factory()->create();

    $this->actingAs($pharmacy['admin'])
        ->postJson('/api/platform/generic-drugs', ['name' => 'Should Fail'])
        ->assertForbidden();

    $this->actingAs($pharmacy['admin'])
        ->getJson('/api/master-drugs')
        ->assertOk()
        ->assertJsonPath('data.0.id', $masterDrug->id);
});

test('duplicate generic drug names are rejected', function () {
    $superadmin = platformSuperadmin();
    GenericDrug::factory()->create(['name' => 'Amoxicillin']);

    $this->actingAs($superadmin)
        ->postJson('/api/platform/generic-drugs', ['name' => 'Amoxicillin'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

test('duplicate master drug barcodes are rejected', function () {
    $superadmin = platformSuperadmin();
    $existing = MasterDrug::factory()->create(['barcode' => '1234567890123']);
    $generic = GenericDrug::factory()->create();

    $this->actingAs($superadmin)
        ->postJson('/api/platform/master-drugs', [
            'generic_drug_id' => $generic->id,
            'brand_name' => 'Duplicate Barcode',
            'barcode' => $existing->barcode,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('barcode');
});
