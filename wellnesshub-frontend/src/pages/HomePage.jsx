import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { ArrowRight } from 'lucide-react'
import EmptyState from '@/components/common/EmptyState'
import ErrorState from '@/components/common/ErrorState'
import LoadingSpinner from '@/components/common/LoadingSpinner'
import Button from '@/components/common/Button'
import ProtocolCard from '@/components/protocols/ProtocolCard'
import ThreadCard from '@/components/threads/ThreadCard'
import { protocolService } from '@/services/protocolService'
import { threadService } from '@/services/threadService'
import { unwrapError, unwrapList } from '@/utils/apiResponse'
import { normalizeProtocol, normalizeThread } from '@/utils/normalizers'

// HomePage gives users a quick view of top protocols and the latest community discussions.
export default function HomePage() {
  const [protocols, setProtocols] = useState([])
  const [threads, setThreads] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    const loadHomeData = async () => {
      setLoading(true)
      setError('')

      try {
        const [protocolResponse, threadResponse] = await Promise.all([
          protocolService.getProtocols({ sort: 'highest_rated', per_page: 6 }),
          threadService.getThreads({ sort: 'recent', per_page: 5 }),
        ])

        setProtocols(unwrapList(protocolResponse).map(normalizeProtocol))
        setThreads(unwrapList(threadResponse).map(normalizeThread))
      } catch (requestError) {
        setError(unwrapError(requestError))
      } finally {
        setLoading(false)
      }
    }

    loadHomeData()
  }, [])

  if (loading) return <LoadingSpinner label="Loading dashboard..." />
  if (error) return <ErrorState message={error} />

  return (
    <div className="space-y-10">
      <section className="rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-600 via-emerald-600 to-emerald-700 p-8 text-white shadow-sm">
        <p className="text-sm font-medium text-emerald-100">WellnessHub</p>
        <h1 className="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">
          Discover practical wellness protocols and learn from community discussions.
        </h1>
        <p className="mt-3 max-w-2xl text-sm text-emerald-50 sm:text-base">
          Explore routines, see what worked for others, and contribute your own insights to help the
          community stay consistent.
        </p>
        <div className="mt-6 flex flex-wrap gap-3">
          <Button as={Link} to="/protocols">
            Browse Protocols
          </Button>
          <Button as={Link} to="/threads" variant="secondary">
            Join Discussions
          </Button>
        </div>
      </section>

      <section>
        <div className="mb-4 flex items-center justify-between">
          <h2 className="text-2xl font-bold tracking-tight text-slate-950">Featured Protocols</h2>
          <Link
            to="/protocols"
            className="inline-flex items-center gap-1 text-sm font-medium text-emerald-700"
          >
            View all
            <ArrowRight className="h-4 w-4" />
          </Link>
        </div>
        {protocols.length ? (
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {protocols.map((protocol) => (
              <ProtocolCard key={protocol.id} protocol={protocol} />
            ))}
          </div>
        ) : (
          <EmptyState
            title="No protocols found."
            description="Create the first protocol to get the community started."
          />
        )}
      </section>

      <section>
        <div className="mb-4 flex items-center justify-between">
          <h2 className="text-2xl font-bold tracking-tight text-slate-950">Recent Threads</h2>
          <Link
            to="/threads"
            className="inline-flex items-center gap-1 text-sm font-medium text-emerald-700"
          >
            View all
            <ArrowRight className="h-4 w-4" />
          </Link>
        </div>
        {threads.length ? (
          <div className="space-y-4">
            {threads.map((thread) => (
              <ThreadCard key={thread.id} thread={thread} />
            ))}
          </div>
        ) : (
          <EmptyState
            title="No discussions yet."
            description="Threads will appear here once people start posting."
          />
        )}
      </section>
    </div>
  )
}
