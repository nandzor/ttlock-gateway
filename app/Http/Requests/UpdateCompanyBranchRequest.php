<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyBranchRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isOperator());
    }

    public function rules(): array {
        return [
            'group_id' => ['required', 'exists:company_groups,id'],
            'branch_name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }

    public function attributes(): array {
        return [
            'group_id' => 'company group',
            'branch_name' => 'branch name',
            'contact_person' => 'contact person',
        ];
    }
}
