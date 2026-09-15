<?php

use App\Modules\Identity\Domain\Enums\UserStatus;
use App\Modules\Tenancy\Domain\Enums\TenantStatus;

test('spa login issues an authenticated session cookie', function () {
    $pharmacy = provisionPharmacy('login');

    spa()->postJson('/api/login', [
        'email' => $pharmacy['admin']->email,
        'password' => 'password',
    ])
        ->assertOk()
        ->assertJsonPath('data.email', $pharmacy['admin']->email)
        ->assertJsonPath('data.tenant_id', $pharmacy['tenant']->id)
        ->assertJsonPath('data.is_pharmacy_admin', true);

    $this->assertAuthenticated();

    spa()->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('data.id', $pharmacy['admin']->id);
});

test('login rejects invalid credentials', function () {
    $pharmacy = provisionPharmacy('badlogin');

    spa()->postJson('/api/login', [
        'email' => $pharmacy['admin']->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();

    $this->assertGuest();
});

test('inactive users cannot log in', function () {
    $pharmacy = provisionPharmacy('inactive');
    $pharmacy['admin']->update(['status' => UserStatus::Inactive]);

    spa()->postJson('/api/login', [
        'email' => $pharmacy['admin']->email,
        'password' => 'password',
    ])->assertForbidden();

    $this->assertGuest();
});

test('users of a suspended tenant cannot log in', function () {
    $pharmacy = provisionPharmacy('suspended');
    $pharmacy['tenant']->update(['status' => TenantStatus::Suspended]);

    spa()->postJson('/api/login', [
        'email' => $pharmacy['admin']->email,
        'password' => 'password',
    ])->assertForbidden();

    $this->assertGuest();
});

test('logout clears the session', function () {
    $pharmacy = provisionPharmacy('logout');

    spa()->postJson('/api/login', [
        'email' => $pharmacy['admin']->email,
        'password' => 'password',
    ])->assertOk();

    spa()->postJson('/api/logout')->assertOk();

    spa()->getJson('/api/user')->assertUnauthorized();
});

test('unauthenticated requests cannot read the current user', function () {
    $this->getJson('/api/user')->assertUnauthorized();
});

test('csrf cookie endpoint is available for the spa', function () {
    $this->get('/sanctum/csrf-cookie')
        ->assertNoContent()
        ->assertCookie('XSRF-TOKEN');
});
