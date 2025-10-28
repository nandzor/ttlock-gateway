<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceMasterRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isOperator());
    }

    public function rules(): array {
        $deviceId = $this->route('device_master');

        return [
            'device_id' => ['required', 'string', 'max:50', Rule::unique('device_masters', 'device_id')->ignore($deviceId, 'device_id')],
            'device_name' => ['required', 'string', 'max:150'],
            'device_type' => ['required', 'in:camera,node_ai,mikrotik,cctv'],
            'branch_id' => ['required', 'exists:company_branches,id'],
            'notes' => ['nullable', 'string'],
            'url' => ['nullable', 'url', 'max:500'],
            'username' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
