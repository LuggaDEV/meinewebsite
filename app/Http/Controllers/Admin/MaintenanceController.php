<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMaintenanceRequest;
use App\Services\MaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function __construct(
        private MaintenanceService $maintenance
    ) {}

    /**
     * Show the maintenance settings form.
     */
    public function edit(): Response
    {
        Gate::authorize('create', \App\Models\About::class);

        $endsAt = $this->maintenance->getEndsAt();

        return Inertia::render('admin/maintenance/Edit', [
            'enabled' => $this->maintenance->isEnabled(),
            'ends_at' => $endsAt?->toIso8601String(),
            'message' => $this->maintenance->getMessage(),
        ]);
    }

    /**
     * Update maintenance settings.
     */
    public function update(UpdateMaintenanceRequest $request): RedirectResponse
    {
        Gate::authorize('create', \App\Models\About::class);

        $validated = $request->validated();

        $endsAt = isset($validated['ends_at'])
            ? \Carbon\CarbonImmutable::parse($validated['ends_at'])
            : null;

        $this->maintenance->set(
            (bool) $validated['enabled'],
            $endsAt,
            $validated['message'] ?? null
        );

        return redirect()->route('admin.maintenance.edit')
            ->with('success', 'Wartungseinstellungen wurden gespeichert.');
    }
}
