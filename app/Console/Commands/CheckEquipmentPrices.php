<?php

namespace App\Console\Commands;

use App\Services\EquipmentPriceCheckService;
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

    public function handle(EquipmentPriceCheckService $checker): int
    {
        $stats = $checker->run();

        $this->info("Checked {$stats['checked']} equipment items, updated {$stats['updated']} prices.");

        return self::SUCCESS;
    }
}
