<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment for admin.
     */
    public function index(): Response
    {
        $equipment = Equipment::latest()->get()->map(function ($item) {
            if ($item->image && !str_starts_with($item->image, 'http')) {
                $item->image = asset('storage/' . $item->image);
            }
            return $item;
        });

        return Inertia::render('admin/equipment/Index', [
            'equipment' => $equipment,
        ]);
    }

    /**
     * Show the form for creating a new equipment item.
     */
    public function create(): Response
    {
        Gate::authorize('create', Equipment::class);

        return Inertia::render('admin/equipment/Create');
    }

    /**
     * Store a newly created equipment item.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Equipment::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'link' => ['required', 'url', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('equipment', 'public');
        }

        Equipment::create($validated);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment erfolgreich erstellt.');
    }

    /**
     * Show the form for editing the specified equipment item.
     */
    public function edit(Equipment $equipment): Response
    {
        Gate::authorize('update', $equipment);

        if ($equipment->image && !str_starts_with($equipment->image, 'http')) {
            $equipment->image = asset('storage/' . $equipment->image);
        }

        return Inertia::render('admin/equipment/Edit', [
            'equipment' => $equipment,
        ]);
    }

    /**
     * Update the specified equipment item.
     */
    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        Gate::authorize('update', $equipment);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'], // 5MB max
            'link' => ['required', 'url', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        if ($request->hasFile('image')) {
            // Altes Bild löschen, falls vorhanden
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }
            $validated['image'] = $request->file('image')->store('equipment', 'public');
        } elseif ($request->has('image') && $request->input('image') === null) {
            // Bild explizit entfernen
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }
            $validated['image'] = null;
        } else {
            // Wenn kein neues Bild hochgeladen wurde, das alte Bild beibehalten
            unset($validated['image']);
        }

        $equipment->update($validated);

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment erfolgreich aktualisiert.');
    }

    /**
     * Remove the specified equipment item.
     */
    public function destroy(Equipment $equipment): RedirectResponse
    {
        Gate::authorize('delete', $equipment);

        // Bild löschen, falls vorhanden
        if ($equipment->image) {
            Storage::disk('public')->delete($equipment->image);
        }

        $equipment->delete();

        return redirect()->route('admin.equipment.index')
            ->with('success', 'Equipment erfolgreich gelöscht.');
    }
}
