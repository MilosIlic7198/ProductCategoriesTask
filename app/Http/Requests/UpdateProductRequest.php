<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateProductRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'product_number' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'upc' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255',
            'regular_price' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Vvalidation messages.
     * 
     */
    public function messages(): array
    {
        return [
            'product_number.string' => 'The product number must be a string.',
            'product_number.max' => 'The product number may not be greater than 255 characters.',
            'category_id.exists' => 'The selected category id does not exist.',
            'department_id.exists' => 'The selected department id does not exist.',
            'manufacturer_id.exists' => 'The selected manufacturer id does not exist.',
            'upc.string' => 'The upc must be a string.',
            'upc.max' => 'The upc may not be greater than 255 characters.',
            'sku.string' => 'The sku must be a string.',
            'sku.max' => 'The sku may not be greater than 255 characters.',
            'regular_price.numeric' => 'The regular price must be a valid number.',
            'sale_price.numeric' => 'The sale price must be a valid number.',
            'description.string' => 'The description must be a string.',
        ];
    }

    /**
     * Handle failed validation.
     * 
     */
    protected function failedValidation(Validator $validator)
    {
        $firstFailedField = $validator->failed();
        $firstField = key($firstFailedField);
        $firstError = $validator->errors()->first($firstField);
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstError,
            'payload' => null,
        ], 422));
    }
}
