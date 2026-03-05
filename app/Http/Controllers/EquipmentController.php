<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment.
     */
    public function index(Request $request): Response
    {
        $allEquipment = Equipment::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $equipment = $allEquipment->map(function (Equipment $item) {
            $image = $item->image && ! str_starts_with($item->image, 'http')
                ? asset('storage/'.$item->image)
                : $item->image;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'image' => $image,
                'link' => $item->link,
                'category' => $item->category,
                'price' => $item->price,
                'original_price' => $item->original_price,
                'discount_percentage' => $item->discount_percentage,
                'created_at' => $item->created_at?->toISOString(),
                'updated_at' => $item->updated_at?->toISOString(),
            ];
        })->values()->all();

        $allCategories = Equipment::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return Inertia::render('equipment/Index', [
            'equipment' => $equipment,
            'allCategories' => $allCategories,
        ]);
    }
}
