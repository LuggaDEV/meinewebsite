<?php

namespace App\Console\Commands;

use App\Services\InstagramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class InstagramFeedTest extends Command
{
    protected $signature = 'instagram:test
                            {--clear : Cache leeren vor dem Test}';

    protected $description = 'Instagram-Feed testen (API-Anfrage, Anzahl Posts)';

    public function handle(InstagramService $instagram): int
    {
        if ($this->option('clear')) {
            Cache::forget('instagram_feed');
            $this->info('Cache geleert.');
        }

        if (empty(config('services.instagram.access_token'))) {
            $this->error('INSTAGRAM_ACCESS_TOKEN muss in .env gesetzt sein.');

            return self::FAILURE;
        }

        $this->info('Rufe Instagram Graph API ab …');

        $feed = $instagram->getMedia(12);

        if (count($feed) > 0) {
            $this->info('Erfolg: '.count($feed).' Post(s) geladen.');
            foreach (array_slice($feed, 0, 3) as $i => $post) {
                $this->line('  '.($i + 1).'. '.($post['permalink'] ?? $post['id']));
            }

            return self::SUCCESS;
        }

        $this->warn('Keine Posts geladen. Prüfe storage/logs/laravel.log für API-Fehler.');
        $this->line('Häufige Ursachen: abgelaufener Token, falsche User-ID oder fehlende Rechte (instagram_basic, pages_show_list).');

        return self::FAILURE;
    }
}
