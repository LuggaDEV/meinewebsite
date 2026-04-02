<?php

namespace App\Http\Controllers;

use App\Services\EquipmentPriceCheckService;
use Illuminate\Http\JsonResponse;

class EquipmentPriceCronController extends Controller
{
    public function __invoke(string $token, EquipmentPriceCheckService $checker): JsonResponse
    {
        $expected = config('equipment.price_refresh_token');

        if (! is_string($expected) || $expected === '' || ! hash_equals($expected, $token)) {
            abort(404);
        }

        $stats = $checker->run();

        return response()->json([
            'checked' => $stats['checked'],
            'updated' => $stats['updated'],
        ]);
    }
}
