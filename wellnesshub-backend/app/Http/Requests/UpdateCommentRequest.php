<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Contracts\Validation\Validator;

class UpdateCommentRequest extends ApiFormRequest
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
            'thread_id' => ['required', 'integer', 'exists:tbl_threads,tbl_thread_id'],
            'parent_id' => ['nullable', 'integer', 'exists:tbl_comments,tbl_comment_id'],
            'body' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $parentId = $this->input('parent_id');
            $threadId = (int) $this->input('thread_id');
            if ($parentId === null) {
                return;
            }

            $parent = Comment::query()->find($parentId);
            if (! $parent || (int) $parent->tbl_comment_thread_id !== $threadId) {
                $validator->errors()->add(
                    'parent_id',
                    'The parent comment must belong to the same thread.'
                );
            }
        });
    }
}
