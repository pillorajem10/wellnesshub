<?php

namespace App\Http\Controllers;

use App\Services\TypesenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TypesenseController extends Controller
{
    use ApiResponse;

    public function reindex(TypesenseService $typesense): JsonResponse
    {
        $typesense->reindexAll();

        return $this->successResponse(null, 'Typesense reindex finished (check logs if the cluster is offline).');
    }
}
