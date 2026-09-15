<?php

use App\Modules\Tenancy\Application\TenantContext;
use App\Modules\Tenancy\Domain\Exceptions\TenantContextNotResolvedException;

test('tenant context starts unresolved', function () {
    $context = new TenantContext;

    expect($context->hasTenant())->toBeFalse()
        ->and($context->isPlatformContext())->toBeFalse()
        ->and($context->hasBranch())->toBeFalse();
});

test('tenant() fails closed when context is missing', function () {
    $context = new TenantContext;

    $context->tenant();
})->throws(TenantContextNotResolvedException::class);

test('platform elevation restores the previous context', function () {
    $context = new TenantContext;

    $result = $context->withoutTenantScopeForAuthorizedPlatformAction(function () use ($context): int {
        expect($context->isPlatformContext())->toBeTrue()
            ->and($context->hasTenant())->toBeFalse();

        return 42;
    });

    expect($result)->toBe(42)
        ->and($context->isPlatformContext())->toBeFalse();
});
