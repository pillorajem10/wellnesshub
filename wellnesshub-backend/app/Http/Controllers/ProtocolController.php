<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProtocolRequest;
use App\Http\Requests\UpdateProtocolRequest;
use App\Models\Protocol;
use App\Services\TypesenseService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProtocolController extends Controller
{
    use ApiResponse;

    public function index(Request $request, TypesenseService $typesense): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $page = max((int) $request->query('page', 1), 1);
        $search = $request->query('search');
        $sort = $request->query('sort', 'recent');

        // If Typesense is configured and a search term is provided, use it for search+sort.
        if (is_string($search) && $search !== '' && $typesense->clientConfigured()) {
            $result = $typesense->searchProtocols($search, is_string($sort) ? $sort : null);
            if ($result !== null) {
                $ids = collect($result['hits'] ?? [])
                    ->map(fn (array $hit) => (int) (($hit['document']['id'] ?? $hit['id'] ?? null)))
                    ->filter()
                    ->values();

                $total = (int) ($result['found'] ?? $ids->count());
                $lastPage = (int) max(1, (int) ceil($total / $perPage));
                $slice = $ids->slice(($page - 1) * $perPage, $perPage)->all();

                $rows = Protocol::query()
                    ->with('author')
                    ->whereIn('tbl_protocol_id', $slice)
                    ->get()
                    ->keyBy('tbl_protocol_id');

                $items = collect($slice)
                    ->map(fn (int $id) => $rows->get($id))
                    ->filter()
                    ->values()
                    ->all();

                return $this->successResponse([
                    'items' => $items,
                    'meta' => [
                        'current_page' => $page,
                        'last_page' => $lastPage,
                        'per_page' => $perPage,
                        'total' => $total,
                    ],
                    'source' => 'typesense',
                ]);
            }
        }

        $query = Protocol::query()->with('author');

        if (is_string($search) && $search !== '') {
            $query->where('tbl_protocol_title', 'like', '%'.$search.'%');
        }

        match ($sort) {
            'most_reviewed' => $query->orderByDesc('tbl_protocol_reviews_count'),
            'highest_rated' => $query->orderByDesc('tbl_protocol_avg_rating'),
            'most_upvoted' => $query->orderByDesc('tbl_protocol_votes_count'),
            default => $query->orderByDesc('tbl_protocol_created_at'),
        };

        $paginator = $query->paginate($perPage);

        return $this->successResponse([
            'items' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'source' => 'database',
        ]);
    }

    public function store(StoreProtocolRequest $request): JsonResponse
    {
        $data = $request->validated();
        $slug = $this->uniqueSlug($data['title']);

        $protocol = Protocol::query()->create([
            'tbl_protocol_title' => $data['title'],
            'tbl_protocol_slug' => $slug,
            'tbl_protocol_content' => $data['content'],
            'tbl_protocol_tags' => $data['tags'] ?? [],
            'tbl_protocol_author_id' => $request->user()->getAuthIdentifier(),
        ]);

        $protocol->load('author');

        return $this->successResponse($protocol, 'Protocol created successfully.', 201);
    }

    public function show(Protocol $protocol): JsonResponse
    {
        $protocol->load([
            'author',
            'reviews.author',
            'threads.author',
        ]);

        return $this->successResponse($protocol);
    }

    public function update(UpdateProtocolRequest $request, Protocol $protocol): JsonResponse
    {
        if ((int) $protocol->tbl_protocol_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $data = $request->validated();

        if ($protocol->tbl_protocol_title !== $data['title']) {
            $protocol->tbl_protocol_slug = $this->uniqueSlug($data['title'], $protocol->getKey());
        }

        $protocol->fill([
            'tbl_protocol_title' => $data['title'],
            'tbl_protocol_content' => $data['content'],
            'tbl_protocol_tags' => $data['tags'] ?? [],
        ]);
        $protocol->save();
        $protocol->load('author');

        return $this->successResponse($protocol, 'Protocol updated successfully.');
    }

    public function destroy(Request $request, Protocol $protocol): JsonResponse
    {
        if ((int) $protocol->tbl_protocol_author_id !== (int) $request->user()->getAuthIdentifier()) {
            return $this->errorResponse('Unauthorized action.', [], 403);
        }

        $protocol->delete();

        return $this->successResponse(null, 'Protocol deleted successfully.');
    }

    private function uniqueSlug(string $title, ?int $ignoreProtocolId = null): string
    {
        $base = Str::slug($title) ?: 'protocol';
        $slug = $base;
        $i = 0;

        while (Protocol::query()
            ->where('tbl_protocol_slug', $slug)
            ->when($ignoreProtocolId, fn ($q) => $q->where('tbl_protocol_id', '!=', $ignoreProtocolId))
            ->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
