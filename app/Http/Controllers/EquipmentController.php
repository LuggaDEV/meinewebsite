<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment.
     */
    public function index(): Response
    {
        $equipment = Equipment::orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                if ($item->image && !str_starts_with($item->image, 'http')) {
                    $item->image = asset('storage/' . $item->image);
                }
                return $item;
            });

        return Inertia::render('equipment/Index', [
            'equipment' => $equipment,
        ]);
    }
}
