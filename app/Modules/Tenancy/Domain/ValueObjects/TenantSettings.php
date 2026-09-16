<?php

namespace App\Modules\Tenancy\Domain\ValueObjects;

final class TenantSettings
{
    public function __construct(
        public readonly int $expiryRedDays = 30,
        public readonly int $expiryYellowDays = 90,
        public readonly bool $blockExpiredSales = true,
        public readonly string $currency = 'TZS',
        public readonly string $timezone = 'Africa/Dar_es_Salaam',
    ) {}

    /**
     * @param  array<string, mixed>  $settings
     */
    public static function fromArray(array $settings): self
    {
        $expiry = is_array($settings['expiry'] ?? null) ? $settings['expiry'] : [];
        $inventory = is_array($settings['inventory'] ?? null) ? $settings['inventory'] : [];

        return new self(
            expiryRedDays: (int) ($expiry['red_days'] ?? 30),
            expiryYellowDays: (int) ($expiry['yellow_days'] ?? 90),
            blockExpiredSales: (bool) ($inventory['block_expired_sales'] ?? true),
            currency: (string) ($settings['currency'] ?? 'TZS'),
            timezone: (string) ($settings['timezone'] ?? 'Africa/Dar_es_Salaam'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'expiry' => [
                'red_days' => $this->expiryRedDays,
                'yellow_days' => $this->expiryYellowDays,
            ],
            'inventory' => [
                'block_expired_sales' => $this->blockExpiredSales,
            ],
            'currency' => $this->currency,
            'timezone' => $this->timezone,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    public function withOverrides(array $overrides): self
    {
        return new self(
            expiryRedDays: (int) ($overrides['expiry_red_days'] ?? $this->expiryRedDays),
            expiryYellowDays: (int) ($overrides['expiry_yellow_days'] ?? $this->expiryYellowDays),
            blockExpiredSales: (bool) ($overrides['block_expired_sales'] ?? $this->blockExpiredSales),
            currency: (string) ($overrides['currency'] ?? $this->currency),
            timezone: (string) ($overrides['timezone'] ?? $this->timezone),
        );
    }
}
