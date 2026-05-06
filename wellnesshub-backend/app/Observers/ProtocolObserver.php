<?php

namespace App\Observers;

use App\Models\Protocol;
use App\Services\TypesenseService;

class ProtocolObserver
{
    public function __construct(
        private TypesenseService $typesense,
    ) {}

    /**
     * Thread rows may be removed by DB cascade without firing Thread model events; drop their search docs here.
     */
    public function deleting(Protocol $protocol): void
    {
        $threadIds = $protocol->threads()->pluck('tbl_thread_id');
        foreach ($threadIds as $id) {
            $this->typesense->deleteThreadDocument((int) $id);
        }
    }

    public function saved(Protocol $protocol): void
    {
        $this->typesense->indexProtocol($protocol);
    }

    public function deleted(Protocol $protocol): void
    {
        $this->typesense->deleteProtocolDocument($protocol->getKey());
    }
}
