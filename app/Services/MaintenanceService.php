<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

class MaintenanceService
{
    private const KEY_ENABLED = 'maintenance.enabled';

    private const KEY_ENDS_AT = 'maintenance.ends_at';

    private const KEY_MESSAGE = 'maintenance.message';

    public function isEnabled(): bool
    {
        return (bool) Cache::get(self::KEY_ENABLED, false);
    }

    public function getEndsAt(): ?CarbonInterface
    {
        $value = Cache::get(self::KEY_ENDS_AT);

        if ($value === null) {
            return null;
        }

        return $value instanceof CarbonInterface ? $value : Carbon::parse($value);
    }

    public function getMessage(): ?string
    {
        $value = Cache::get(self::KEY_MESSAGE);

        return $value === null || $value === '' ? null : (string) $value;
    }

    public function set(bool $enabled, ?CarbonInterface $endsAt = null, ?string $message = null): void
    {
        Cache::put(self::KEY_ENABLED, $enabled);
        Cache::put(self::KEY_ENDS_AT, $endsAt);
        Cache::put(self::KEY_MESSAGE, $message ?? '');
    }
}
