<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMaintenanceRequest;
use App\Services\MaintenanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
    public function edit(Request $request): Response
    {
        Gate::authorize('create', \App\Models\About::class);

        return Inertia::render('admin/maintenance/Edit', $this->editProps($request));
    }

    /**
     * Update maintenance settings.
     */
    public function update(UpdateMaintenanceRequest $request): RedirectResponse
    {
        Gate::authorize('create', \App\Models\About::class);

        $validated = $request->validated();

        $endsAt = isset($validated['ends_at'])
            ? \Carbon\CarbonImmutable::parse($validated['ends_at'], 'Europe/Berlin')
            : null;

        $backgroundVideoUrl = $this->maintenance->getBackgroundVideoUrl();
        if ($request->hasFile('background_video')) {
            $path = $request->file('background_video')->store('maintenance', 'public');
            $backgroundVideoUrl = Storage::url($path);
        } elseif ($request->has('background_video_url')) {
            $url = $validated['background_video_url'] ?? null;
            $backgroundVideoUrl = ($url !== null && (string) $url !== '') ? (string) $url : null;
        }

        $this->maintenance->set(
            (bool) $validated['enabled'],
            $endsAt,
            $validated['message'] ?? null,
            $backgroundVideoUrl
        );

        return redirect()->route('admin.maintenance.edit')
            ->with('success', 'Wartungseinstellungen wurden gespeichert.');
    }

    /**
     * @return array<string, mixed>
     */
    private function editProps(Request $request): array
    {
        $endsAt = $this->maintenance->getEndsAt();
        $old = $request->old();
        $endsAtFormatted = $endsAt?->timezone('Europe/Berlin')->format('Y-m-d\TH:i');

        return [
            'enabled' => array_key_exists('enabled', $old) ? filter_var($old['enabled'], FILTER_VALIDATE_BOOLEAN) : $this->maintenance->isEnabled(),
            'ends_at' => isset($old['ends_at']) ? (string) $old['ends_at'] : $endsAtFormatted,
            'message' => $old['message'] ?? $this->maintenance->getMessage(),
            'background_video_url' => $old['background_video_url'] ?? $this->maintenance->getBackgroundVideoUrl(),
        ];
    }
}
