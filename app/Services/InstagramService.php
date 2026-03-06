<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    private const CACHE_KEY = 'instagram_feed';

    private const CACHE_TTL_SECONDS = 3600;

    private const GRAPH_API_VERSION = 'v21.0';

    private const MEDIA_FIELDS = 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp';

    /**
     * @return array<int, array{id: string, media_type: string, media_url: string, permalink: string, video_url?: string, caption?: string}>
     */
    public function getMedia(int $limit = 12): array
    {
        $accessToken = trim((string) config('services.instagram.access_token'));

        if ($accessToken === '') {
            return [];
        }

        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn () => $this->fetchMedia($accessToken, $limit)
        );
    }

    /**
     * @return array<int, array{id: string, media_type: string, media_url: string, permalink: string, video_url?: string, caption?: string}>
     */
    private function fetchMedia(string $accessToken, int $limit): array
    {
        $baseUrl = 'https://graph.instagram.com/'.self::GRAPH_API_VERSION;
        $headers = ['Authorization' => 'Bearer '.$accessToken];

        $response = Http::withHeaders($headers)->get("{$baseUrl}/me/media", [
            'fields' => self::MEDIA_FIELDS,
            'limit' => $limit,
        ]);

        if (! $response->successful()) {
            $body = $response->body();
            $logContext = [
                'status' => $response->status(),
                'body' => $body,
                'token_length' => strlen($accessToken),
            ];
            if ($response->status() === 400 && str_contains($body, 'Cannot parse access token')) {
                $logContext['hint'] = 'Token in .env in Anführungszeichen setzen: INSTAGRAM_ACCESS_TOKEN="..." oder neuen Token in Meta erzeugen.';
            }
            Log::warning('Instagram Graph API request failed', $logContext);

            return [];
        }

        $data = $response->json();
        $items = $data['data'] ?? [];

        if (! is_array($items) || count($items) === 0) {
            return [];
        }

        $feed = [];
        $idsToFetch = [];

        foreach ($items as $item) {
            $entry = $this->normalizeMediaItem($item);
            if ($entry !== null) {
                $feed[] = $entry;
            } elseif (isset($item['id'])) {
                $idsToFetch[] = $item['id'];
            }
        }

        if (count($feed) === 0 && count($idsToFetch) > 0) {
            $feed = $this->fetchMediaDetails($baseUrl, $headers, $idsToFetch, $limit);
        }

        return $feed;
    }

    /**
     * Fetch full media details by ID when /media returns only ids.
     *
     * @param  array<string, string>  $headers
     * @param  array<int, string>  $mediaIds
     * @return array<int, array{id: string, media_type: string, media_url: string, permalink: string, video_url?: string, caption?: string}>
     */
    private function fetchMediaDetails(string $baseUrl, array $headers, array $mediaIds, int $limit): array
    {
        $mediaIds = array_slice($mediaIds, 0, $limit);
        $feed = [];

        $responses = Http::pool(fn ($pool) => collect($mediaIds)->map(
            fn ($id) => $pool->as((string) $id)->withHeaders($headers)->get("{$baseUrl}/{$id}", [
                'fields' => self::MEDIA_FIELDS,
            ])
        )->all());

        foreach ($responses as $id => $response) {
            if (! $response->successful()) {
                continue;
            }
            $entry = $this->normalizeMediaItem($response->json());
            if ($entry !== null) {
                $feed[] = $entry;
            }
        }

        return $feed;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array{id: string, media_type: string, media_url: string, permalink: string, video_url?: string, caption?: string}|null
     */
    private function normalizeMediaItem(array $item): ?array
    {
        $id = $item['id'] ?? null;
        $permalink = $item['permalink'] ?? null;

        if ($id === null || $permalink === null) {
            return null;
        }

        $mediaType = $item['media_type'] ?? 'IMAGE';
        $mediaUrl = $item['media_url'] ?? null;
        $thumbnailUrl = $item['thumbnail_url'] ?? null;
        $caption = isset($item['caption']) ? (string) $item['caption'] : null;

        if ($mediaType === 'VIDEO') {
            $posterUrl = $thumbnailUrl ?? $mediaUrl;
            if ($posterUrl === null || $posterUrl === '') {
                return null;
            }
            $entry = [
                'id' => (string) $id,
                'media_type' => 'VIDEO',
                'permalink' => (string) $permalink,
                'media_url' => (string) $posterUrl,
                'video_url' => $mediaUrl !== null && $mediaUrl !== '' ? (string) $mediaUrl : null,
            ];
        } else {
            $imageUrl = $mediaUrl ?? $thumbnailUrl;
            if ($imageUrl === null || $imageUrl === '') {
                return null;
            }
            $entry = [
                'id' => (string) $id,
                'media_type' => (string) $mediaType,
                'permalink' => (string) $permalink,
                'media_url' => (string) $imageUrl,
            ];
        }

        if ($caption !== null && $caption !== '') {
            $entry['caption'] = $caption;
        }

        return $entry;
    }
}
