<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Validation\Rule;

class UpdateReviewRequest extends ApiFormRequest
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
        /** @var Review $review */
        $review = $this->route('review');

        return [
            'protocol_id' => [
                'required',
                'integer',
                'exists:tbl_protocols,tbl_protocol_id',
                Rule::unique('tbl_reviews', 'tbl_review_protocol_id')
                    ->ignore($review->getKey(), 'tbl_review_id')
                    ->where('tbl_review_author_id', auth()->id()),
            ],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string'],
        ];
    }
}
