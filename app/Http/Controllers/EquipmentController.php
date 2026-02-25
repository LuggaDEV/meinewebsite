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

        $allEquipment->transform(function ($item) {
            if ($item->image && ! str_starts_with($item->image, 'http')) {
                $item->image = asset('storage/'.$item->image);
            }

            return $item;
        });

        $allCategories = Equipment::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        return Inertia::render('equipment/Index', [
            'equipment' => $allEquipment,
            'allCategories' => $allCategories,
        ]);
    }
}
