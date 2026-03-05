<?php

namespace App\Console\Commands;

use App\Models\Equipment;
use App\Services\EquipmentPriceService;
use Illuminate\Console\Command;

class CheckEquipmentPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment:check-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch current prices from equipment product links and update discount display';

    public function handle(EquipmentPriceService $priceService): int
    {
        $equipment = Equipment::query()->whereNotNull('link')->where('link', '!=', '')->get();
        $updated = 0;

        foreach ($equipment as $item) {
            $result = $priceService->getPriceAndDiscountFromUrl($item->link);
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

        $this->info("Checked {$equipment->count()} equipment items, updated {$updated} prices.");

        return self::SUCCESS;
    }
}
