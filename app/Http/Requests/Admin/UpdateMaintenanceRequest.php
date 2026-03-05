<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'ends_at' => ['nullable', 'date', 'after:now'],
            'message' => ['nullable', 'string', 'max:2000'],
            'background_video_url' => ['nullable', 'string', 'max:2000'],
            'background_video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm', 'max:51200'],
        ];
    }
}
