<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\CompanySettingsUpdateRequest;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Show the company settings page.
     */
    public function edit(): Response
    {
        $settings = CompanySetting::current();

        return Inertia::render('settings/Company', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the company settings.
     */
    public function update(CompanySettingsUpdateRequest $request): RedirectResponse
    {
        $settings = CompanySetting::current();
        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        // Remove logo from data if not uploaded
        unset($data['logo']);

        // Add updated_by
        $data['updated_by'] = $request->user()->id;

        // Update settings
        $settings->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Configuración de empresa actualizada.')]);

        return to_route('company.edit');
    }
}
