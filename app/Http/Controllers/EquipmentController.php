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
        $search = $request->query('search', '');
        $categories = $request->query('categories', []);

        $query = Equipment::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($categories)) {
            // Accept comma-separated string or array
            if (is_string($categories)) {
                $categories = array_filter(explode(',', $categories));
            }
            $query->whereIn('category', $categories);
        }

        $paginated = $query->orderBy('category')
            ->orderBy('name')
            ->paginate(12);

        // Transform images on the underlying collection
        $paginated->getCollection()->transform(function ($item) {
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
            'equipment' => $paginated,
            'filters' => [
                'search' => $search,
                'categories' => is_array($categories) ? $categories : [],
            ],
            'allCategories' => $allCategories,
        ]);
    }
}
