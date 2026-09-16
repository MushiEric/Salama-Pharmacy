<?php

use App\Modules\Inventory\Domain\Enums\BatchEligibility;
use App\Modules\Inventory\Domain\Enums\ExpiryStatus;
use App\Modules\Inventory\Domain\Policies\BatchSaleEligibilityPolicy;
use App\Modules\Tenancy\Domain\ValueObjects\TenantSettings;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->policy = new BatchSaleEligibilityPolicy;
    $this->settings = new TenantSettings(expiryRedDays: 30, expiryYellowDays: 90);
    $this->now = Carbon::parse('2026-06-01');
});

test('a batch with no expiry date is always green and eligible', function () {
    expect($this->policy->expiryStatus(null, $this->settings, $this->now))->toBe(ExpiryStatus::Green)
        ->and($this->policy->eligibility(null, $this->settings, $this->now))->toBe(BatchEligibility::Eligible);
});

test('a batch expiring within the red window is red', function () {
    $expiresAt = $this->now->copy()->addDays(10);

    expect($this->policy->expiryStatus($expiresAt, $this->settings, $this->now))->toBe(ExpiryStatus::Red);
});

test('a batch expiring within the yellow window but past red is yellow', function () {
    $expiresAt = $this->now->copy()->addDays(60);

    expect($this->policy->expiryStatus($expiresAt, $this->settings, $this->now))->toBe(ExpiryStatus::Yellow);
});

test('a batch expiring beyond the yellow window is green', function () {
    $expiresAt = $this->now->copy()->addDays(200);

    expect($this->policy->expiryStatus($expiresAt, $this->settings, $this->now))->toBe(ExpiryStatus::Green);
});

test('a batch not yet expired is eligible regardless of how close it is', function () {
    $expiresAt = $this->now->copy()->addDay();

    expect($this->policy->eligibility($expiresAt, $this->settings, $this->now))->toBe(BatchEligibility::Eligible);
});

test('an expired batch is blocked when the tenant blocks expired sales', function () {
    $settings = $this->settings->withOverrides(['block_expired_sales' => true]);
    $expiresAt = $this->now->copy()->subDay();

    expect($this->policy->eligibility($expiresAt, $settings, $this->now))->toBe(BatchEligibility::Blocked);
});

test('an expired batch is only a warning when the tenant allows expired sales', function () {
    $settings = $this->settings->withOverrides(['block_expired_sales' => false]);
    $expiresAt = $this->now->copy()->subDay();

    expect($this->policy->eligibility($expiresAt, $settings, $this->now))->toBe(BatchEligibility::Warning);
});
