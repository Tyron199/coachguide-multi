<?php

namespace App\Http\Requests\Tenant\Coach;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomFrameworkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('coach');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
                'min:10',
            ],
            'category' => [
                'required',
                Rule::in(['models', 'tools']),
            ],
            'subcategory' => [
                'nullable',
                'string',
                'max:100',
            ],
            'best_for' => [
                'nullable',
                'array',
                'max:10',
            ],
            'best_for.*' => [
                'string',
                'max:100',
            ],
            'fields' => [
                'nullable',
                'array',
                'max:20',
            ],
            'fields.*.key' => [
                'nullable',
                'string',
                'max:255', // UUIDs are longer than 100 chars
            ],
            'fields.*.title' => [
                'nullable',
                'string',
                'max:255',
                'min:3',
            ],
            'fields.*.description' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Framework name is required.',
            'name.min' => 'Framework name must be at least 3 characters.',
            'name.max' => 'Framework name cannot exceed 255 characters.',
            
            'description.required' => 'Framework description is required.',
            'description.min' => 'Description must be at least 10 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            
            'category.required' => 'Please select a category (Models or Tools).',
            'category.in' => 'Category must be either "models" or "tools".',
            
            'subcategory.max' => 'Subcategory cannot exceed 100 characters.',
            
            'best_for.array' => 'Best for must be an array of coaching types.',
            'best_for.max' => 'Cannot select more than 10 coaching types.',
            'best_for.*.string' => 'Each coaching type must be a string.',
            'best_for.*.max' => 'Coaching type cannot exceed 100 characters.',
            
            'fields.max' => 'Framework cannot have more than 20 fields.',
            
            
            'fields.*.title.required' => 'Field title is required.',
            'fields.*.title.min' => 'Field title must be at least 3 characters.',
            'fields.*.title.max' => 'Field title cannot exceed 255 characters.',
            
            'fields.*.description.max' => 'Field description cannot exceed 500 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'framework name',
            'description' => 'framework description',
            'category' => 'category',
            'subcategory' => 'subcategory',
            'best_for' => 'coaching types',
            'fields' => 'framework fields',
            'fields.*.key' => 'field key',
            'fields.*.title' => 'field title',
            'fields.*.description' => 'field description',
        ];
    }
}
