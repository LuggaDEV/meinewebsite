<?php

namespace App\Services;

use App\Models\Equipment;

class EquipmentPriceCheckService
{
    public function __construct(
        private EquipmentPriceService $priceService
    ) {}

    /**
     * Fetch prices for all equipment with a non-empty link and persist changes.
     *
     * @return array{checked: int, updated: int}
     */
    public function run(): array
    {
        $equipment = Equipment::query()->whereNotNull('link')->where('link', '!=', '')->get();
        $updated = 0;

        foreach ($equipment as $item) {
            $result = $this->priceService->getPriceAndDiscountFromUrl($item->link);
            $newPriceStr = $result['price'];
            $item->last_price_checked_at = now();

            if ($newPriceStr === null) {
                $item->discount_percentage = null;
                $item->original_price = null;
                $item->save();

                continue;
            }

            $item->price = $newPriceStr;
            $item->original_price = $result['original_price'] ?? null;
            $item->discount_percentage = $result['discount_percentage'] ?? null;

            $item->save();
            $updated++;
        }

        return [
            'checked' => $equipment->count(),
            'updated' => $updated,
        ];
    }
}
