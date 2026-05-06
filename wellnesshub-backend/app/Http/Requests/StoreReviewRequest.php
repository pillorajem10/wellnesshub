<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreReviewRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'protocol_id' => [
                'required',
                'integer',
                'exists:tbl_protocols,tbl_protocol_id',
                Rule::unique('tbl_reviews', 'tbl_review_protocol_id')
                    ->where('tbl_review_author_id', auth()->id()),
            ],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string'],
        ];
    }
}
