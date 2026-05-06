<?php

namespace App\Observers;

use App\Models\Thread;
use App\Services\TypesenseService;

class ThreadObserver
{
    public function __construct(
        private TypesenseService $typesense,
    ) {}

    public function saved(Thread $thread): void
    {
        $this->typesense->indexThread($thread);
    }

    public function deleted(Thread $thread): void
    {
        $this->typesense->deleteThreadDocument($thread->getKey());
    }
}
