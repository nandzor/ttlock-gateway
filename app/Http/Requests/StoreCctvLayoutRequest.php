<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCctvLayoutRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array {
        return [
            'layout_name' => ['required', 'string', 'max:150'],
            'layout_type' => ['required', 'in:4-window,6-window,8-window'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'positions' => ['required', 'array'],
            'positions.*.position_number' => ['required', 'integer', 'min:1'],
            'positions.*.branch_id' => ['required', 'exists:company_branches,id'],
            'positions.*.device_id' => ['required', 'exists:device_masters,device_id'],
            'positions.*.position_name' => ['nullable', 'string', 'max:100'],
            'positions.*.is_enabled' => ['nullable', 'boolean'],
            'positions.*.quality' => ['nullable', 'in:low,medium,high'],
            'positions.*.resolution' => ['nullable', 'string', 'max:20'],
        ];
    }
}
