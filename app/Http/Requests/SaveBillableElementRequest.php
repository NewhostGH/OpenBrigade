<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a billable element (create and update). The permission (29) is
 * enforced by the route middleware; section authorisation happens in the
 * controller. French decimal commas are accepted for the price.
 */
class SaveBillableElementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('EF_PRICE'))) {
            $this->merge(['EF_PRICE' => str_replace([',', ' '], ['.', ''], $this->input('EF_PRICE'))]);
        }
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'TEF_CODE' => ['required', 'string', 'exists:type_element_facturable,TEF_CODE'],
            'EF_NAME' => ['required', 'string', 'max:60'],
            'EF_PRICE' => ['required', 'numeric', 'min:0', 'max:99999'],
            'S_ID' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /** @return array<string,string> */
    public function attributes(): array
    {
        return [
            'TEF_CODE' => __('billable_element.col_type'),
            'EF_NAME' => __('billable_element.col_name'),
            'EF_PRICE' => __('billable_element.col_price'),
            'S_ID' => __('billable_element.col_section'),
        ];
    }
}
