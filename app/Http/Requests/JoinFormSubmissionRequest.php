<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JoinFormSubmissionRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'participation_pathway_id' => [
                'required',
                'integer',
                // Only a pathway the frontend can actually offer, so a hidden or
                // removed one cannot be submitted by id.
                Rule::exists('participation_pathways', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'message' => ['required', 'string'],
        ];
    }
}
