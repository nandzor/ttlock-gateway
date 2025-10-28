<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyGroupRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        // Only admin can update company groups
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        $groupId = $this->route('company_group');

        return [
            'group_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('company_groups', 'group_name')->ignore($groupId)
            ],
            'province' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array {
        return [
            'group_name' => 'group name',
            'province' => 'province',
            'description' => 'description',
            'status' => 'status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array {
        return [
            'group_name.required' => 'The group name field is required.',
            'group_name.unique' => 'This group name already exists.',
            'province.required' => 'The province field is required.',
        ];
    }
}
