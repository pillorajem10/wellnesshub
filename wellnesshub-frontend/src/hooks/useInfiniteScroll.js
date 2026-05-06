import { useEffect, useRef } from 'react'

export function useInfiniteScroll({ hasMore, loading, onLoadMore }) {
  const loadMoreRef = useRef(null)

  useEffect(() => {
    const target = loadMoreRef.current
    if (!target) return undefined

    const observer = new IntersectionObserver(
      (entries) => {
        const firstEntry = entries[0]
        if (!firstEntry?.isIntersecting) return
        if (loading || !hasMore) return
        onLoadMore()
      },
      {
        rootMargin: '120px 0px',
        threshold: 0,
      }
    )

    observer.observe(target)

    return () => {
      observer.disconnect()
    }
  }, [hasMore, loading, onLoadMore])

  return loadMoreRef
}
