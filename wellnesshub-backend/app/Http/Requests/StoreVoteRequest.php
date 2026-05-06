<?php

namespace App\Http\Requests;

class StoreVoteRequest extends ApiFormRequest
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
            'votable_type' => ['required', 'string', 'in:thread,comment'],
            'votable_id' => ['required', 'integer'],
            'value' => ['required', 'integer', 'in:1,-1'],
        ];
    }
}
