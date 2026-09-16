<?php

namespace App\Modules\Inventory\Domain\Policies;

use App\Modules\Inventory\Domain\Enums\BatchEligibility;
use App\Modules\Inventory\Domain\Enums\ExpiryStatus;
use App\Modules\Tenancy\Domain\ValueObjects\TenantSettings;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class BatchSaleEligibilityPolicy
{
    public function expiryStatus(?CarbonInterface $expiresAt, TenantSettings $settings, ?CarbonInterface $now = null): ExpiryStatus
    {
        if ($expiresAt === null) {
            return ExpiryStatus::Green;
        }

        $now ??= Carbon::now();
        $daysUntilExpiry = $now->diffInDays($expiresAt, false);

        if ($daysUntilExpiry < $settings->expiryRedDays) {
            return ExpiryStatus::Red;
        }

        if ($daysUntilExpiry < $settings->expiryYellowDays) {
            return ExpiryStatus::Yellow;
        }

        return ExpiryStatus::Green;
    }

    public function eligibility(?CarbonInterface $expiresAt, TenantSettings $settings, ?CarbonInterface $now = null): BatchEligibility
    {
        $now ??= Carbon::now();

        if ($expiresAt === null || $expiresAt->isAfter($now)) {
            return BatchEligibility::Eligible;
        }

        return $settings->blockExpiredSales ? BatchEligibility::Blocked : BatchEligibility::Warning;
    }
}
