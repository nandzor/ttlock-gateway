{{--
Filter Select Component Documentation

This component provides a specialized select dropdown for filtering with auto-submit functionality.

Usage:
<x-filter-select
  name="field_name"
  label="Field Label"
  :options="$optionsArray"
  :selected="request()->get('field_name')"
  placeholder="All Items"
  :autoSubmit="true" />

Props:
- name: Field name for the select
- label: Label text (optional)
- options: Array of options (key => value pairs)
- selected: Currently selected value
- placeholder: Placeholder text (default: "All")
- required: Whether field is required (default: false)
- disabled: Whether field is disabled (default: false)
- error: Error message (optional)
- hint: Hint text (optional)
- autoSubmit: Whether to auto-submit form on change (default: false)

Features:
- Auto-submit functionality for instant filtering
- Consistent styling with other form components
- Error handling and validation
- Responsive design
- Icon support for error states

Example:
<x-filter-select
  name="status"
  label="Status"
  :options="['1' => 'Active', '0' => 'Inactive']"
  :selected="request()->get('status')"
  placeholder="All Status"
  :autoSubmit="true" />
--}}
