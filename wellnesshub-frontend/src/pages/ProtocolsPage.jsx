import { useCallback, useEffect, useMemo, useState } from 'react'
import { Search } from 'lucide-react'
import EmptyState from '@/components/common/EmptyState'
import ErrorState from '@/components/common/ErrorState'
import LoadingSpinner from '@/components/common/LoadingSpinner'
import ProtocolCard from '@/components/protocols/ProtocolCard'
import ProtocolFilters from '@/components/protocols/ProtocolFilters'
import Card from '@/components/common/Card'
import { useInfiniteScroll } from '@/hooks/useInfiniteScroll'
import { useDebouncedValue } from '@/hooks/useDebouncedValue'
import { protocolService } from '@/services/protocolService'
import { searchService } from '@/services/searchService'
import { unwrapError, unwrapList, unwrapPagination } from '@/utils/apiResponse'
import { normalizeProtocol } from '@/utils/normalizers'

function mergeUniqueById(previousItems, newItems) {
  const itemMap = new Map()

  previousItems.forEach((item) => itemMap.set(item.id, item))
  newItems.forEach((item) => itemMap.set(item.id, item))

  return Array.from(itemMap.values())
}

// ProtocolsPage is the main browsing screen for wellness protocols, including search and sorting.
export default function ProtocolsPage() {
  const [protocols, setProtocols] = useState([])
  const [searchInput, setSearchInput] = useState('')
  const debouncedSearch = useDebouncedValue(searchInput, 350)
  const [sort, setSort] = useState('recent')
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

  const loadProtocols = useCallback(async () => {
    const nextLoadingState = page === 1 ? setLoading : setLoadingMore
    nextLoadingState(true)
    setError('')

    try {
      let response

      if (isSearching) {
        // Search currently returns a single result set, so infinite scroll is disabled while searching.
        response = await searchService.searchProtocols(searchQuery)
      } else {
        response = await protocolService.getProtocols({
          sort,
          per_page: 9,
          page,
        })
      }

      const normalizedItems = unwrapList(response).map(normalizeProtocol)

      setProtocols((previous) => {
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
  }, [isSearching, page, searchQuery, sort])

  useEffect(() => {
    loadProtocols()
  }, [loadProtocols])

  const loadMoreRef = useInfiniteScroll({
    hasMore,
    loading: loading || loadingMore,
    onLoadMore: () => setPage((current) => current + 1),
  })

  const handleSearchChange = (value) => {
    setSearchInput(value)
  }

  const handleSortChange = (value) => {
    setSort(value)
    setProtocols([])
    setPage(1)
  }

  useEffect(() => {
    setProtocols([])
    setPage(1)
  }, [searchQuery])

  return (
    <div className="space-y-6">
      <section>
        <h1 className="text-3xl font-bold tracking-tight text-slate-950">Explore Protocols</h1>
        <p className="mt-1 text-sm text-slate-600">
          Browse structured wellness guides and discover routines that work.
        </p>
      </section>

      <Card className="p-4">
        <ProtocolFilters
          search={searchInput}
          sort={sort}
          onSearchChange={handleSearchChange}
          onSortChange={handleSortChange}
        />
      </Card>

      {loading ? <LoadingSpinner label="Loading protocols..." /> : null}
      {!loading && error ? <ErrorState message={error} /> : null}

      {!loading && !error && !protocols.length ? (
        <EmptyState
          icon={Search}
          title="No protocols found."
          description="Try adjusting your search or sort options."
        />
      ) : null}

      {!loading && !error && protocols.length ? (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {protocols.map((protocol) => (
            <ProtocolCard key={protocol.id} protocol={protocol} />
          ))}
        </div>
      ) : null}

      {!loading && !error && protocols.length ? (
        <div ref={loadMoreRef} className="h-2 w-full" />
      ) : null}

      {loadingMore ? <LoadingSpinner label="Loading more protocols..." /> : null}

      {!loading && !loadingMore && !hasMore && protocols.length ? (
        <p className="text-center text-sm text-slate-500">You have reached the end.</p>
      ) : null}
    </div>
  )
}
