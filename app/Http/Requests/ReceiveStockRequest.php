<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates incoming API requests to securely add new stock (Receive) into a specific location.
 * Implements strict type checking and validation rules to prevent bad data.
 */
class ReceiveStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * We'll assume the policy checks authorization before the controller method.
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
            'item_id'            => ['required', 'integer', 'exists:items,id'],
            'location_id'        => ['required', 'integer', 'exists:locations,id'],
            'quantity'           => ['required', 'integer', 'min:1'],
            'batch_number'       => ['required', 'string', 'max:255'],
            'unit_cost'          => ['nullable', 'numeric', 'min:0'],
            'selling_price'      => ['nullable', 'numeric', 'min:0'],
            'manufacturing_date' => ['nullable', 'date', 'before_or_equal:today'],
            'expiry_date'        => ['nullable', 'date', 'after:manufacturing_date'],
            'supplier_name'      => ['nullable', 'string', 'max:255'],
            'reason'             => ['nullable', 'string', 'max:500'],
        ];
    }
}
