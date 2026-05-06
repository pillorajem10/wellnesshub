import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { MessageCircle, Star, User } from 'lucide-react'
import EmptyState from '@/components/common/EmptyState'
import ErrorState from '@/components/common/ErrorState'
import LoadingSpinner from '@/components/common/LoadingSpinner'
import Badge from '@/components/common/Badge'
import Button from '@/components/common/Button'
import Card from '@/components/common/Card'
import ReviewForm from '@/components/protocols/ReviewForm'
import ProtocolReviewList from '@/components/protocols/ProtocolReviewList'
import RelatedThreads from '@/components/protocols/RelatedThreads'
import { useAuth } from '@/hooks/useAuth'
import { protocolService } from '@/services/protocolService'
import { reviewService } from '@/services/reviewService'
import { unwrapApiData, unwrapError } from '@/utils/apiResponse'
import { normalizeProtocol } from '@/utils/normalizers'

// ProtocolDetailPage displays a single protocol with related reviews and discussion threads.
export default function ProtocolDetailPage() {
  const { id } = useParams()
  const { isAuthenticated } = useAuth()

  const [protocol, setProtocol] = useState(null)
  const [loading, setLoading] = useState(true)
  const [reviewLoading, setReviewLoading] = useState(false)
  const [error, setError] = useState('')

  const loadProtocol = async () => {
    const response = await protocolService.getProtocol(id)
    const data = unwrapApiData(response)
    setProtocol(normalizeProtocol(data))
  }

  useEffect(() => {
    const initializePage = async () => {
      setLoading(true)
      setError('')

      try {
        await loadProtocol()
      } catch (requestError) {
        setError(unwrapError(requestError))
      } finally {
        setLoading(false)
      }
    }

    initializePage()
  }, [id])

  // The API returns tbl_* column names, but Laravel validation expects clean request keys when creating or updating records.
  const handleReviewSubmit = async ({ rating, feedback }) => {
    setReviewLoading(true)
    setError('')

    try {
      await reviewService.createReview({
        protocol_id: Number(id),
        rating,
        feedback,
      })
      await loadProtocol()
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      setReviewLoading(false)
    }
  }

  if (loading) return <LoadingSpinner label="Loading protocol details..." />
  if (error) return <ErrorState message={error} />
  if (!protocol)
    return <EmptyState title="Protocol not found." description="It may have been removed." />

  return (
    <div className="grid gap-6 lg:grid-cols-3">
      <section className="space-y-6 lg:col-span-2">
        <Card className="p-6">
          <h1 className="text-3xl font-bold tracking-tight text-slate-950">{protocol.title}</h1>
          <p className="mt-2 inline-flex items-center gap-2 text-sm text-slate-600">
            <User className="h-4 w-4" />
            {protocol.author.fullName}
          </p>

          <div className="mt-3 flex flex-wrap gap-2">
            {protocol.tags.map((tag) => (
              <Badge key={tag}>{tag}</Badge>
            ))}
          </div>

          <p className="mt-4 whitespace-pre-wrap leading-relaxed text-slate-700">
            {protocol.content}
          </p>
        </Card>

        <Card className="space-y-4 p-6">
          <h2 className="text-xl font-bold tracking-tight text-slate-950">Reviews</h2>
          {isAuthenticated ? (
            <ReviewForm onSubmit={handleReviewSubmit} loading={reviewLoading} />
          ) : (
            <p className="text-sm text-slate-600">Log in to write a review.</p>
          )}
          <ProtocolReviewList reviews={protocol.reviews} />
        </Card>
      </section>

      <aside className="space-y-4">
        <Card className="p-5">
          <h3 className="text-lg font-bold text-slate-950">Protocol stats</h3>
          <div className="mt-3 space-y-2">
            <p className="flex items-center gap-2 text-sm text-slate-600">
              <Star className="h-4 w-4 text-amber-500" />
              {protocol.avgRating} average rating
            </p>
            <p className="flex items-center gap-2 text-sm text-slate-600">
              <MessageCircle className="h-4 w-4 text-emerald-600" />
              {protocol.reviewsCount} reviews
            </p>
          </div>
          {isAuthenticated ? (
            <Link to={`/protocols/${protocol.id}/threads/create`} className="mt-4 block">
              <Button className="w-full">Create Thread</Button>
            </Link>
          ) : null}
        </Card>

        <Card className="space-y-3 p-5">
          <h3 className="text-lg font-bold text-slate-950">Related Threads</h3>
          <RelatedThreads threads={protocol.threads} />
        </Card>
      </aside>
    </div>
  )
}
