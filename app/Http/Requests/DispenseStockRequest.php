<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates dispensing requests to prevent over-dispensing or unauthorized outputs.
 */
class DispenseStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id'     => ['required', 'integer', 'exists:items,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
