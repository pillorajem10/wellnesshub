import { useCallback, useEffect, useMemo, useState } from 'react'
import { Search } from 'lucide-react'
import EmptyState from '@/components/common/EmptyState'
import ErrorState from '@/components/common/ErrorState'
import Input from '@/components/common/Input'
import LoadingSpinner from '@/components/common/LoadingSpinner'
import Card from '@/components/common/Card'
import ThreadCard from '@/components/threads/ThreadCard'
import { useInfiniteScroll } from '@/hooks/useInfiniteScroll'
import { useDebouncedValue } from '@/hooks/useDebouncedValue'
import { threadService } from '@/services/threadService'
import { searchService } from '@/services/searchService'
import { unwrapError, unwrapList, unwrapPagination } from '@/utils/apiResponse'
import { normalizeThread } from '@/utils/normalizers'

function mergeUniqueById(previousItems, newItems) {
  const itemMap = new Map()
  previousItems.forEach((item) => itemMap.set(item.id, item))
  newItems.forEach((item) => itemMap.set(item.id, item))
  return Array.from(itemMap.values())
}

// ThreadsPage is the central feed for protocol-related discussions with search and sorting tools.
export default function ThreadsPage() {
  const [threads, setThreads] = useState([])
  const [searchInput, setSearchInput] = useState('')
  const debouncedSearch = useDebouncedValue(searchInput, 350)
  const [page, setPage] = useState(1)
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    perPage: 0,
    total: 0,
  })
  const [loading, setLoading] = useState(true)
  const [loadingMore, setLoadingMore] = useState(false)
  const [error, setError] = useState('')

  const searchQuery = debouncedSearch.trim()
  const isSearching = Boolean(searchQuery)

  const hasMore = useMemo(() => {
    if (isSearching) return false
    return pagination.currentPage < pagination.lastPage
  }, [isSearching, pagination.currentPage, pagination.lastPage])

  const loadThreads = useCallback(async () => {
    const nextLoadingState = page === 1 ? setLoading : setLoadingMore
    nextLoadingState(true)
    setError('')

    try {
      let response

      if (isSearching) {
        // Search currently returns a single result set, so infinite scroll is disabled while searching.
        response = await searchService.searchThreads(searchQuery, sort)
      } else {
        response = await threadService.getThreads({
          per_page: 9,
          page,
          sort,
        })
      }

      const normalizedItems = unwrapList(response).map(normalizeThread)

      setThreads((previous) => {
        if (page === 1) return normalizedItems
        return mergeUniqueById(previous, normalizedItems)
      })

      if (isSearching) {
        setPagination({
          currentPage: 1,
          lastPage: 1,
          perPage: normalizedItems.length,
          total: normalizedItems.length,
        })
      } else {
        setPagination(unwrapPagination(response))
      }
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      nextLoadingState(false)
    }
  }, [isSearching, page, searchQuery])

  useEffect(() => {
    loadThreads()
  }, [loadThreads])

  const loadMoreRef = useInfiniteScroll({
    hasMore,
    loading: loading || loadingMore,
    onLoadMore: () => setPage((current) => current + 1),
  })

  const handleSearchChange = (value) => {
    setSearchInput(value)
  }

  useEffect(() => {
    setThreads([])
    setPage(1)
  }, [searchQuery])

  return (
    <div className="space-y-6">
      <section>
        <h1 className="text-3xl font-bold tracking-tight text-slate-950">Join Discussions</h1>
        <p className="mt-1 text-sm text-slate-600">
          Read real experiences and share what worked for your wellness goals.
        </p>
      </section>

      <Card className="p-4">
        <Input
          id="thread-search"
          label="Search threads"
          value={searchInput}
          onChange={(event) => handleSearchChange(event.target.value)}
          placeholder='Try "sleep" or "routine"'
        />
      </Card>

      {loading ? <LoadingSpinner label="Loading discussions..." /> : null}
      {!loading && error ? <ErrorState message={error} /> : null}

      {!loading && !error && !threads.length ? (
        <EmptyState
          icon={Search}
          title="No discussions found."
          description="Try adjusting your search keyword."
        />
      ) : null}

      {!loading && !error && threads.length ? (
        <div className="space-y-4">
          {threads.map((thread) => (
            <ThreadCard key={thread.id} thread={thread} />
          ))}
        </div>
      ) : null}

      {!loading && !error && threads.length ? (
        <div ref={loadMoreRef} className="h-2 w-full" />
      ) : null}

      {loadingMore ? <LoadingSpinner label="Loading more discussions..." /> : null}

      {!loading && !loadingMore && !hasMore && threads.length ? (
        <p className="text-center text-sm text-slate-500">You have reached the end.</p>
      ) : null}
    </div>
  )
}
